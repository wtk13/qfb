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
