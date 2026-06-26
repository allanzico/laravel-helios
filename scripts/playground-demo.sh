#!/usr/bin/env bash
set -euo pipefail

PORT="${PORT:-8001}"
BASE_URL="http://127.0.0.1:${PORT}"

curl -fsS "$BASE_URL/demo/request" >/dev/null
curl -fsS "$BASE_URL/demo/slow-request" >/dev/null
curl -sS "$BASE_URL/demo/error" >/dev/null || true
curl -fsS "$BASE_URL/demo/jobs/fail" >/dev/null
curl -fsS "$BASE_URL/demo/action" >/dev/null

cat <<TEXT
Demo data generated.

Open:
  $BASE_URL/helios
TEXT
