#Requires -Version 5.1
<#
.SYNOPSIS
    Finalize a meeting: record outcomes, post summary to Telegram, mark complete.
#>

[CmdletBinding()]
param(
    [Parameter(Mandatory=$true)][string]$MeetingId,
    [Parameter(Mandatory=$true)][string]$Summary,
    # Optional: @(@{ type=..; description=..; assigned_to=..; gateway=..; due_date=.. })
    [object[]]$Outcomes = @()
)

$ErrorActionPreference = "Stop"
Import-Module "$PSScriptRoot\ControlAgencyApi.psm1" -Force

foreach ($o in $Outcomes) {
    $body = @{
        outcome_type = $o.type
        description  = $o.description
    }
    if ($o.assigned_to) { $body.assigned_to = $o.assigned_to }
    if ($o.gateway)     { $body.gateway     = $o.gateway }
    if ($o.due_date)    { $body.due_date    = $o.due_date }
    Invoke-CaaApi -Method POST -Path "/meetings/$MeetingId/outcomes" -Body $body | Out-Null
}

$r = Invoke-CaaApi -Method POST -Path "/meetings/$MeetingId/complete" -Body @{ summary = $Summary }

Write-Host "Meeting $MeetingId complete. Summary posted, $($Outcomes.Count) outcomes recorded."
return $r.data
