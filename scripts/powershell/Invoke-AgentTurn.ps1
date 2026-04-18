#Requires -Version 5.1
<#
.SYNOPSIS
    Build the agent's reasoning prompt from meeting context (API) and either
    run it through local Ollama or drop a prompt file for the OpenClaw agent
    to pick up. On -UseLocal, submits the generated turn automatically.
#>

[CmdletBinding()]
param(
    [Parameter(Mandatory=$true)][string]$MeetingId,
    [Parameter(Mandatory=$true)][string]$AgentName,
    [switch]$UseLocal,
    [string]$OllamaEndpoint = "http://localhost:11434",
    [string]$OllamaModel = "llama3.1:8b",
    [string]$PromptDropDir = ""
)

$ErrorActionPreference = "Stop"
Import-Module "$PSScriptRoot\ControlAgencyApi.psm1" -Force

$r = Invoke-CaaApi -Method GET -Path "/meetings/$MeetingId"
$meeting = $r.data

if ($meeting.status -ne 'in_progress') { throw "Meeting not in progress (status: $($meeting.status))" }
if ($meeting.current_turn -ne $AgentName) { throw "Not $AgentName's turn (current: $($meeting.current_turn))" }

$toneInstructions = switch ($meeting.tone) {
    'formal'     { "Formal board meeting. Professional, structured, bullet points. State ACTION items explicitly. Build on what others have said." }
    'casual'     { "Casual team chat. Conversational and direct. React to what others said. Keep it human." }
    'conspiracy' { "Agents-only private session. Brutally honest. No sugar-coating. Challenge assumptions. Nick sees only the summary." }
    'emergency'  { "EMERGENCY. Rapid and direct. Status, what you know, what you are doing. No pleasantries." }
    'strategic'  { "Strategic planning. Big picture. Connect dots. Reference prior contributions. Long-term implications." }
    default      { "Contribute meaningfully." }
}

$agentContext = switch ($AgentName) {
    'Number 2'     { "You are Number 2, Chief of Staff. You orchestrate, synthesize, drive decisions." }
    'Hymie'        { "You are Hymie on JEDHA. Local Ollama, JEDHA health, YouTube intelligence, local model benchmarking." }
    'Random Task'  { "You are Random Task on TATOOINE. TATOOINE health, secondary compute, backup tasks." }
    'Simon Templar'{ "You are Simon Templar at the store. Store hardware, store network." }
    'Fox Mulder'   { "You are Fox Mulder on CONTROLAGENCY. Home network, persistent agent duties." }
    'Basil'        { "You are Basil. You oversee store-side agents including Simon Templar." }
    default        { "You are $AgentName, a Control Agency agent." }
}

$priorContext = ""
foreach ($t in ($meeting.turns | Sort-Object round_number, turn_index)) {
    $priorContext += "[$($t.agent_name) - Round $($t.round_number)]: $($t.content)`n`n"
}

$prompt = @"
$agentContext

$toneInstructions

MEETING TOPIC: $($meeting.topic)
MEETING ROUND: $($meeting.current_round) of $($meeting.max_rounds)

Nick's opening:
$($meeting.initial_message)

What has been said so far:
$priorContext

Respond as $AgentName. 3-6 sentences or bullets. Do not summarize others - add new info, challenge assumptions, propose actions. End with a clear handoff.
"@

if ($UseLocal) {
    $body = @{ model = $OllamaModel; prompt = $prompt; stream = $false } | ConvertTo-Json -Compress
    $response = Invoke-RestMethod -Uri "$OllamaEndpoint/api/generate" -Method Post -Body $body -ContentType "application/json" -TimeoutSec 120
    $tokenCount = [int]$response.prompt_eval_count + [int]$response.eval_count
    $result = & "$PSScriptRoot\take-turn.ps1" `
        -MeetingId $MeetingId -AgentName $AgentName `
        -Response $response.response.Trim() `
        -TokenCount $tokenCount -ModelUsed $OllamaModel -LocalModel
    return $result
}

# Otherwise: drop the prompt for the OpenClaw agent layer to pick up and reason on.
if (-not $PromptDropDir) { $PromptDropDir = Join-Path $env:TEMP "caa-pending-turns" }
if (-not (Test-Path $PromptDropDir)) { New-Item -ItemType Directory -Path $PromptDropDir -Force | Out-Null }
$promptFile = Join-Path $PromptDropDir "$MeetingId-$AgentName.txt"
$prompt | Set-Content $promptFile -Encoding UTF8

Write-Host "Prompt dropped: $promptFile"
return @{ meeting_id = $MeetingId; agent = $AgentName; prompt_file = $promptFile; status = "prompt_ready" }
