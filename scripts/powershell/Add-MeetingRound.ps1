#Requires -Version 5.1
<#
.SYNOPSIS
    Add another round to a meeting. Nick's "continue" command.
#>

[CmdletBinding()]
param(
    [Parameter(Mandatory=$true)][string]$MeetingId,
    [string]$Focus = ""
)

$ErrorActionPreference = "Stop"
Import-Module "$PSScriptRoot\ControlAgencyApi.psm1" -Force

$body = @{}
if ($Focus) { $body.focus = $Focus }

$r = Invoke-CaaApi -Method POST -Path "/meetings/$MeetingId/rounds" -Body $body
$m = $r.data

Write-Host "Round $($m.current_round) started on $($m.meeting_id). First agent: $($m.current_turn)"
return $m
