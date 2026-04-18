#Requires -Version 5.1
<#
.SYNOPSIS
    Submit a turn to a meeting. Server handles Telegram + turn advancement.
.EXAMPLE
    .\take-turn.ps1 -MeetingId "2026-04-18-..." -AgentName "Hymie" -Response "All green on JEDHA..."
#>

[CmdletBinding()]
param(
    [Parameter(Mandatory=$true)][string]$MeetingId,
    [Parameter(Mandatory=$true)][string]$AgentName,
    [Parameter(Mandatory=$true)][string]$Response,
    [int]$TokenCount = 0,
    [string]$ModelUsed = "",
    [switch]$LocalModel
)

$ErrorActionPreference = "Stop"
Import-Module "$PSScriptRoot\ControlAgencyApi.psm1" -Force

$body = @{
    agent_name   = $AgentName
    content      = $Response
    token_count  = $TokenCount
    model_used   = $ModelUsed
    local_model  = [bool]$LocalModel
}

$r = Invoke-CaaApi -Method POST -Path "/meetings/$MeetingId/turns" -Body $body

Write-Host "Turn recorded for $AgentName. Next: $(if ($r.next_agent) { $r.next_agent } else { 'SUMMARY' })"

return @{
    meeting_id = $MeetingId
    next_agent = $r.next_agent
    next_round = $r.next_round
    status     = $r.status
}
