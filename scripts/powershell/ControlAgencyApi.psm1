#Requires -Version 5.1
<#
.SYNOPSIS
    Shared HTTP helper for Control Agency API (api.greyglassholdings.com).
    Reads CAA_API_BASE and CAA_API_KEY from env.
#>

function Get-CaaConfig {
    $base = $env:CAA_API_BASE
    if (-not $base) { $base = "https://api.greyglassholdings.com" }
    $key = $env:CAA_API_KEY
    if (-not $key) { throw "CAA_API_KEY env var not set" }
    return @{ Base = $base.TrimEnd('/'); Key = $key }
}

function Invoke-CaaApi {
    [CmdletBinding()]
    param(
        [Parameter(Mandatory=$true)][ValidateSet('GET','POST','PATCH','DELETE')][string]$Method,
        [Parameter(Mandatory=$true)][string]$Path,
        [object]$Body = $null,
        [int]$TimeoutSec = 30
    )
    $cfg = Get-CaaConfig
    $uri = "$($cfg.Base)/api$Path"
    $headers = @{
        Authorization = "Bearer $($cfg.Key)"
        Accept        = "application/json"
    }
    $params = @{
        Uri         = $uri
        Method      = $Method
        Headers     = $headers
        TimeoutSec  = $TimeoutSec
        ErrorAction = 'Stop'
    }
    if ($Body -ne $null) {
        $params.Body = ($Body | ConvertTo-Json -Depth 10 -Compress)
        $params.ContentType = "application/json"
    }
    return Invoke-RestMethod @params
}

Export-ModuleMember -Function Get-CaaConfig, Invoke-CaaApi
