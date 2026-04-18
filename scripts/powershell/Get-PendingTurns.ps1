#Requires -Version 5.1
<#
.SYNOPSIS
    Poll for meetings awaiting this agent's turn. Run on the gateway's
    heartbeat loop. If anything is returned, the gateway should hand
    each meeting to its reasoning layer.
#>

[CmdletBinding()]
param(
    [Parameter(Mandatory=$true)][string]$AgentName
)

$ErrorActionPreference = "Stop"
Import-Module "$PSScriptRoot\ControlAgencyApi.psm1" -Force

$r = Invoke-CaaApi -Method GET -Path "/agents/$([uri]::EscapeDataString($AgentName))/pending"
return $r.data
