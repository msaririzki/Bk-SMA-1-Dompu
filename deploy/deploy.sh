#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."
git pull --ff-only
docker compose build --pull
docker compose up -d --remove-orphans
docker compose exec -T app php artisan migrate --force
docker compose exec -T app php artisan optimize
docker compose exec -T app php artisan queue:restart
docker compose ps
