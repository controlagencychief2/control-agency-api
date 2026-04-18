#Requires -Version 5.1
<#
.SYNOPSIS
    Issue an order to a gateway. Server assigns order_ref.
#>

[CmdletBinding()]
param(
    [Parameter(Mandatory=$true)]
    [ValidateSet('JEDHA','TATOOINE','CONTROLAGENCY','BASIL','STORE')]
    [string]$Gateway,

    [Parameter(Mandatory=$true)][string]$Description,
    [string]$MeetingId = "",
    [string]$AssignedBy = "Number 2",
    [string]$Notes = ""
)

$ErrorActionPreference = "Stop"
Import-Module "$PSScriptRoot\ControlAgencyApi.psm1" -Force

$body = @{
    gateway     = $Gateway
    description = $Description
    assigned_by = $AssignedBy
}
if ($MeetingId) { $body.meeting_id = $MeetingId }
if ($Notes)     { $body.notes      = $Notes }

$r = Invoke-CaaApi -Method POST -Path "/gateway-orders" -Body $body
$o = $r.data

Write-Host "Order $($o.order_ref) issued to $Gateway"
return $o
