# PowerShell clients for control-agency-api

Thin HTTP clients. No secrets in files. Secrets come from env.

## Setup (per gateway)

```powershell
# user-level env so scheduled tasks and heartbeats see it
[Environment]::SetEnvironmentVariable('CAA_API_BASE', 'https://api.greyglassholdings.com', 'User')
[Environment]::SetEnvironmentVariable('CAA_API_KEY',  '<the bearer key>', 'User')
```

## Scripts

| Script | What it does |
| --- | --- |
| `start-meeting.ps1` | `POST /meetings` — creates meeting + participants, posts opener |
| `take-turn.ps1` | `POST /meetings/{id}/turns` — atomic turn advance + Telegram post |
| `Add-MeetingRound.ps1` | `POST /meetings/{id}/rounds` — "continue" |
| `generate-summary.ps1` | records outcomes, posts summary to Telegram, marks complete |
| `Write-GatewayOrders.ps1` | `POST /gateway-orders` |
| `Invoke-AgentTurn.ps1` | builds reasoning prompt; if `-UseLocal` runs Ollama and submits; else drops a prompt file |
| `Get-PendingTurns.ps1` | `GET /agents/{name}/pending` — poll on heartbeat |
| `ControlAgencyApi.psm1` | shared auth + `Invoke-CaaApi` helper |

## Typical gateway loop

```powershell
# in each agent's heartbeat
$pending = & "$scripts\Get-PendingTurns.ps1" -AgentName "Hymie"
foreach ($m in $pending) {
    & "$scripts\Invoke-AgentTurn.ps1" -MeetingId $m.meeting_id -AgentName "Hymie" -UseLocal
}
```
