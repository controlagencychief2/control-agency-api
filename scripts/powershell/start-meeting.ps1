#Requires -Version 5.1
<#
.SYNOPSIS
    Create a new Control Agency meeting via the API.
.EXAMPLE
    .\start-meeting.ps1 -MeetingType daily-standup -InitialMessage "Morning team"
#>

[CmdletBinding()]
param(
    [Parameter(Mandatory=$true)]
    [ValidateSet('daily-standup','lunch-chat','conspiracy','emergency','weekly-review','continuous')]
    [string]$MeetingType,

    [string]$InitialMessage = "",
    [string]$Topic = "",
    [string]$CreatedBy = "Number 2"
)

$ErrorActionPreference = "Stop"
Import-Module "$PSScriptRoot\ControlAgencyApi.psm1" -Force

$defs = @{
    'daily-standup'  = @{ Topic = "Daily Standup - Priorities and Tasks";       Tone = "formal";     MaxRounds = 2;   GroupId = "-5154319592"; Participants = @("Number 2","Hymie","Random Task") }
    'lunch-chat'     = @{ Topic = "Lunch Break - Casual Discussion";            Tone = "casual";     MaxRounds = 3;   GroupId = "-5128562491"; Participants = @("Number 2","Hymie","Random Task") }
    'conspiracy'     = @{ Topic = "After Hours Strategy - Agents Only";         Tone = "conspiracy"; MaxRounds = 2;   GroupId = "-5004107142"; Participants = @("Number 2","Hymie","Random Task") }
    'emergency'      = @{ Topic = "Emergency Response";                         Tone = "emergency";  MaxRounds = 1;   GroupId = "-5224827557"; Participants = @("Number 2","Hymie","Random Task") }
    'weekly-review'  = @{ Topic = "Weekly Review - Strategic Planning";         Tone = "strategic";  MaxRounds = 3;   GroupId = "-5269460519"; Participants = @("Number 2","Hymie","Random Task") }
    'continuous'     = @{ Topic = "KAOS - Continuous Coordination Channel";     Tone = "casual";     MaxRounds = 999; GroupId = "-5126961892"; Participants = @("Number 2","Hymie","Random Task") }
}

$def = $defs[$MeetingType]
if ($Topic) { $def.Topic = $Topic }

$meetingId = "$(Get-Date -Format 'yyyy-MM-dd')-$MeetingType-$(Get-Date -Format 'HHmmss')"

$body = @{
    meeting_id        = $meetingId
    meeting_type      = $MeetingType
    topic             = $def.Topic
    tone              = $def.Tone
    max_rounds        = $def.MaxRounds
    telegram_group_id = $def.GroupId
    initial_message   = $InitialMessage
    created_by        = $CreatedBy
    participants      = $def.Participants
}

$r = Invoke-CaaApi -Method POST -Path "/meetings" -Body $body
$meeting = $r.data

Write-Host "Meeting started: $($meeting.meeting_id)"
Write-Host "First turn: $($meeting.current_turn)"

return @{
    meeting_id = $meeting.meeting_id
    first_turn = $meeting.current_turn
    group_id   = $meeting.telegram_group_id
}
