# Running `reddit:scout` Locally Against Prod

## Why this exists

The Reddit public scraper (`old.reddit.com/r/{sub}/new.json`) returns HTTP 403 when called from the prod server's datacenter IP. Until the dedicated Reddit account has enough age/karma to use the OAuth API safely, we work around this by running the scout from a residential IP (this laptop) and writing the discovered threads directly into the prod database.

The rest of the Reddit pipeline continues to run on prod's scheduler:
- `reddit:draft` -- daily at 07:00 UTC, consumes threads scouted earlier
- `reddit:publish` -- every 15 minutes
- `reddit:strategist` -- weekly

This means: **scout locally at least once before 07:00 UTC each day** so the drafter has fresh threads to work with.

## Prerequisites

- SSH access to prod with the alias `qfb` (confirm with `ssh qfb echo ok`)
- Docker Desktop + Sail containers running (`./vendor/bin/sail ps`)
- Prod Postgres credentials (fetched in setup step 2)

## One-time setup

**1. Create the script directory and helper script.**

```bash
mkdir -p scripts
```

Save the following as `scripts/scout-reddit-prod.sh`:

```bash
#!/bin/bash
set -euo pipefail

PROJECT_DIR="/Users/rybinski/Sites/qfb"
ENV_FILE="$PROJECT_DIR/.reddit-scout-env"
SSH_SOCK="/tmp/qfb-scout.sock"

cd "$PROJECT_DIR"

if [[ ! -f "$ENV_FILE" ]]; then
  echo "Missing $ENV_FILE -- see docs/reddit-scout-local.md" >&2
  exit 1
fi

# Load prod DB credentials into the environment
set -a
source "$ENV_FILE"
set +a

# Open SSH tunnel (control socket so we can cleanly close it later)
ssh -fNM -S "$SSH_SOCK" -L 15432:localhost:5432 qfb
trap 'ssh -S "$SSH_SOCK" -O exit qfb 2>/dev/null || true' EXIT

# Run scout inside the Sail container, overriding DB connection to point at
# the tunnel. host.docker.internal resolves to the Mac host from inside Docker.
./vendor/bin/sail exec \
  -e DB_HOST=host.docker.internal \
  -e DB_PORT=15432 \
  -e "DB_DATABASE=$DB_DATABASE" \
  -e "DB_USERNAME=$DB_USERNAME" \
  -e "DB_PASSWORD=$DB_PASSWORD" \
  laravel.test php artisan reddit:scout
```

Make it executable:

```bash
chmod +x scripts/scout-reddit-prod.sh
```

**2. Copy prod DB credentials into a local env file.**

Fetch them from prod:

```bash
ssh qfb 'grep -E "^DB_(DATABASE|USERNAME|PASSWORD)=" /var/www/quickfeedback/.env'
```

Paste the three lines into `.reddit-scout-env` in the project root:

```env
DB_DATABASE=<from prod>
DB_USERNAME=<from prod>
DB_PASSWORD=<from prod>
```

**3. Gitignore the credentials file.**

Already added to `.gitignore` -- verify:

```bash
grep reddit-scout-env .gitignore
```

**4. Clear local Laravel config cache** (so env var overrides take effect):

```bash
./vendor/bin/sail artisan config:clear
```

## Daily usage

Run once per day, ideally before 07:00 UTC:

```bash
./scripts/scout-reddit-prod.sh
```

Expected output:

```
Scouting Reddit for new threads via public scraper (no API credentials)...
Found N new threads.
```

`N > 0` means it worked. The SSH tunnel opens, scout runs, tunnel closes automatically.

## Verify it worked

Check prod's thread count:

```bash
ssh qfb 'cd /var/www/quickfeedback && php artisan tinker --execute="echo \App\Infrastructure\Persistence\Eloquent\RedditThreadModel::count() . PHP_EOL;"'
```

Or open the admin dashboard: <https://quickfeedback.app/admin/reddit>

## Troubleshooting

**`Found 0 new threads`**

Check whether your home IP can actually reach Reddit:

```bash
curl -sI -A "Mozilla/5.0" https://old.reddit.com/r/smallbusiness/new.json | head -1
```

Expect `HTTP/2 200`. If 403, your home IP has been rate-limited -- wait an hour and retry.

If Reddit is fine but scout still finds nothing, the keyword filter may be too narrow. The filter lives in `app/Application/Command/Reddit/ScoutThreads.php:31-45`. Also note that threads are dropped if score `< 1` or older than 72h (`ScoutThreads.php:63-70`).

**`SQLSTATE[08006]` / connection refused**

SSH tunnel didn't open. Check:

```bash
ps aux | grep "ssh.*15432"
```

If nothing, run the script again -- the tunnel setup failed silently. Try `ssh qfb echo ok` to confirm SSH works at all.

**`password authentication failed for user`**

`.reddit-scout-env` is stale. Re-fetch from prod:

```bash
ssh qfb 'grep -E "^DB_(DATABASE|USERNAME|PASSWORD)=" /var/www/quickfeedback/.env'
```

**Tunnel doesn't close after script exits**

Nuke it manually:

```bash
ssh -S /tmp/qfb-scout.sock -O exit qfb 2>/dev/null || pkill -f "ssh.*15432:localhost:5432"
```

**Sail container not running**

```bash
./vendor/bin/sail up -d
```

## Caveats

- **Laptop-dependent.** If you don't run the script, the whole prod Reddit pipeline is starved -- drafter produces nothing, publisher has nothing to post. Put a reminder on your calendar until this is automated.
- **72-hour freshness window.** Missing a day is fine (the next run still catches any post that's <72h old). Missing three days loses coverage.
- **Manual step, by design.** Don't automate via launchd/cron until you've run it manually for at least a week and confirmed the output is consistently useful. Automating a broken pipeline just hides problems.
