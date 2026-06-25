#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
APP_DIR="$ROOT_DIR/playground"
PORT="${PORT:-8001}"

if [ ! -d "$APP_DIR/vendor" ]; then
    composer install --working-dir="$APP_DIR"
fi

if [ ! -f "$APP_DIR/.env" ]; then
    cp "$APP_DIR/.env.example" "$APP_DIR/.env"
    php "$APP_DIR/artisan" key:generate
fi

if [ ! -f "$APP_DIR/database/database.sqlite" ]; then
    touch "$APP_DIR/database/database.sqlite"
fi

php "$APP_DIR/artisan" migrate --force
php "$APP_DIR/artisan" helios:sync-tasks

cat <<TEXT

Helios playground is starting.

Demo app:  http://127.0.0.1:${PORT}
Helios:    http://127.0.0.1:${PORT}/helios

Generate demo data from the homepage links or run:
  bash scripts/playground-demo.sh

TEXT

php "$APP_DIR/artisan" serve --host=127.0.0.1 --port="$PORT"
