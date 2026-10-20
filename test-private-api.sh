#!/usr/bin/env bash
set -euo pipefail

# Override with env vars instead of editing this file, e.g.:
#   API_URL=https://your-domain.com/api/get-api-key API_KEY=... API_SECRET=... ./test-private-api.sh
API_URL="${API_URL:-http://127.0.0.1:8000/api/get-api-key}"
# Test credentials (match .env). Keep this file out of Git.
API_KEY="${API_KEY:-ak_live_5737cebf2bec8b5821b7892af1cdbe5a}"
API_SECRET="${API_SECRET:-c9a3e1e7c57750e74083a054f9876a42e903b494c3fd9351644dbfd86a8be88a}"

TIMESTAMP="$(date +%s)"
NONCE="$(openssl rand -hex 16)"
BODY_HASH="$(printf '' | sha256sum | awk '{print $1}')"

PAYLOAD="$(printf 'GET\n/api/get-api-key\n%s\n%s\n%s' \
    "$TIMESTAMP" "$NONCE" "$BODY_HASH")"

SIGNATURE="$(printf '%s' "$PAYLOAD" \
    | openssl dgst -sha256 -hmac "$API_SECRET" \
    | awk '{print $NF}')"

curl --fail-with-body --silent --show-error \
    --request GET "$API_URL" \
    --header "Accept: application/json" \
    --header "X-API-KEY: $API_KEY" \
    --header "X-TIMESTAMP: $TIMESTAMP" \
    --header "X-NONCE: $NONCE" \
    --header "X-SIGNATURE: $SIGNATURE"
