# Deploy runbook — control-agency-api

Target: `api.greyglassholdings.com` on VPS.
Repo path: `/var/www/control-agency-api`.

## First deploy after the meetings refactor

The schema was squashed — the two original meetings migrations were replaced with one consolidated file plus four new tables. Both original tables were empty, so `migrate:fresh` is safe **for meetings** but will also drop heartbeats, benchmarks, cache, jobs, and sessions. If there is any production data in those tables, back them up first.

### 0. Pre-flight on the VPS

```bash
cd /var/www/control-agency-api
sudo -u www-data git status
sudo -u www-data git fetch origin
```

Confirm the working tree is clean (only the usual storage `.gitignore` churn is acceptable).

### 1. Back up

```bash
mkdir -p ~/backups/control-agency-api
STAMP=$(date +%Y%m%d-%H%M%S)
mysqldump --databases control_agency_api > ~/backups/control-agency-api/db-$STAMP.sql
cp .env ~/backups/control-agency-api/.env-$STAMP
git rev-parse HEAD > ~/backups/control-agency-api/HEAD-$STAMP.txt
```

### 2. Pull

```bash
sudo -u www-data git checkout master
sudo -u www-data git pull --ff-only origin master
```

If pull is not fast-forward, stop and investigate.

### 3. Dependencies

```bash
sudo -u www-data composer install --no-dev --optimize-autoloader
```

### 4. Env changes

Add the following keys to `/var/www/control-agency-api/.env`. Values for the Telegram tokens can be pulled from the legacy PS1 scripts on `\\STARKILLERBASE\d$\shared\meetings\scripts\*.ps1` (rotate them after this deploy is confirmed working).

```ini
TELEGRAM_API_BASE=https://api.telegram.org
TELEGRAM_TIMEOUT=10
TELEGRAM_BOT_TOKEN_NUMBER2=
TELEGRAM_BOT_TOKEN_HYMIE=
TELEGRAM_BOT_TOKEN_RANDOMTASK=
TELEGRAM_BOT_TOKEN_SIMONTEMPLAR=
TELEGRAM_BOT_TOKEN_FOXMULDER=
TELEGRAM_BOT_TOKEN_BASIL=
```

`AGENT_API_KEY` is already set — leave it.

### 5. Migrate

Meetings + meeting_turns tables are empty. `migrate:fresh` wipes everything and re-runs. If heartbeats / benchmarks hold data you need to keep, do the selective path instead (see Rollback notes).

```bash
sudo -u www-data php artisan migrate:fresh --force
```

Expected: 9 migrations run (users, cache, jobs, heartbeats, meetings, meeting_participants, meeting_outcomes, meeting_costs, gateway_orders, benchmark_runs).

### 6. Cache

```bash
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
```

### 7. Reload PHP-FPM

```bash
sudo systemctl reload php8.3-fpm
```

### 8. Smoke test (from any machine with CAA_API_KEY)

```powershell
$env:CAA_API_BASE = "https://api.greyglassholdings.com"
$env:CAA_API_KEY  = "<AGENT_API_KEY value>"

# 1. Auth works
Invoke-RestMethod -Uri "$env:CAA_API_BASE/api/heartbeats" -Headers @{Authorization="Bearer $env:CAA_API_KEY"}

# 2. Meeting creation + Telegram opener
$m = & .\scripts\powershell\start-meeting.ps1 -MeetingType lunch-chat -InitialMessage "Deploy smoke test"

# 3. Turn submission + Telegram post + atomic advance
& .\scripts\powershell\take-turn.ps1 -MeetingId $m.meeting_id -AgentName "Number 2" -Response "All green post-deploy."

# 4. Polling
& .\scripts\powershell\Get-PendingTurns.ps1 -AgentName "Hymie"   # should include $m.meeting_id
```

Expected:
- Opener lands in the Telegram group for `lunch-chat`.
- Turn #1 posts as Number 2 to the same group.
- Polling for Hymie returns the meeting.

If any step fails, see Rollback.

### 9. Rotate tokens

Once the API is confirmed working end-to-end, rotate:
- All six Telegram bot tokens (BotFather → `/revoke` then `/token`). Update `.env` and `systemctl reload php8.3-fpm`.
- `AGENT_API_KEY` (generate new, update `.env`, push new value to every gateway's `CAA_API_KEY` env var).

## Subsequent deploys

No schema resets needed. Standard flow:

```bash
cd /var/www/control-agency-api
sudo -u www-data git pull --ff-only origin master
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache && sudo -u www-data php artisan route:cache
sudo systemctl reload php8.3-fpm
```

## Rollback

```bash
cd /var/www/control-agency-api
# 1. Revert code to the last known-good commit
sudo -u www-data git reset --hard <HEAD-STAMP>
# 2. Restore DB
mysql control_agency_api < ~/backups/control-agency-api/db-<STAMP>.sql
# 3. Rebuild caches
sudo -u www-data php artisan config:cache && sudo -u www-data php artisan route:cache
sudo systemctl reload php8.3-fpm
```

## Selective migration (if you need to preserve heartbeats/benchmarks)

Instead of `migrate:fresh`:

```sql
-- As root on MySQL
DROP TABLE IF EXISTS meeting_turns;
DROP TABLE IF EXISTS meetings;
DELETE FROM migrations WHERE migration IN (
  '2026_04_04_020418_create_meetings_table',
  '2026_04_04_020423_create_meeting_turns_table'
);
```

Then `php artisan migrate --force` runs only the new migrations.

## Post-deploy cleanup (one-time, non-urgent)

- Archive `\\STARKILLERBASE\d$\shared\meetings\scripts\*.ps1` and `meetings.db` — legacy, replaced by the `scripts/powershell/` clients in this repo.
- Update each gateway's heartbeat to call `Get-PendingTurns.ps1` and hand results to `Invoke-AgentTurn.ps1`.

## Quick reference

| Concern | Command |
| --- | --- |
| App logs | `sudo -u www-data tail -f storage/logs/laravel.log` |
| Nginx logs | `sudo tail -f /var/log/nginx/error.log` |
| Route list | `sudo -u www-data php artisan route:list --path=api` |
| DB console | `mysql control_agency_api` |
| Cert expiry | `sudo certbot certificates` |
