#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
TEACHER_NAME="${EDUCANET_TEACHER_NAME:-Adrian Staněk}"
TEACHER_TEAM_ID="${EDUCANET_TEACHER_TEAM_ID:-educanet-teachers}"
TEACHER_TEAM_NAME="${EDUCANET_TEACHER_TEAM_NAME:-EDUCANET učitelé}"
TEACHER_ROLE="${EDUCANET_TEACHER_ROLE:-teacher}"
KEY="${1:-}"

if [[ -n "${EDUCANET_SECRETS_FILE:-}" ]]; then
  TARGET="$EDUCANET_SECRETS_FILE"
elif [[ "$ROOT" =~ ^(/var/www/clients/[^/]+/web[0-9]+)/web(/|$) ]]; then
  # ISPConfig: keep the secret in the account's private directory.
  TARGET="${BASH_REMATCH[1]}/private/educanet.secrets.php"
elif [[ "$ROOT" == /www/wwwroot/* ]]; then
  # aaPanel.
  TARGET="/www/server/educanet/educanet.secrets.php"
else
  TARGET="$(dirname "$ROOT")/educanet.secrets.php"
fi

if [[ -z "$KEY" ]]; then
  if command -v openssl >/dev/null 2>&1; then
    KEY="$(openssl rand -base64 48 | tr -d '\n' | tr '/+' '_-')"
  elif command -v php >/dev/null 2>&1; then
    KEY="$(php -r 'echo rtrim(strtr(base64_encode(random_bytes(48)), "+/", "-_"), "=");')"
  else
    echo "ERROR: openssl ani php nejsou dostupné; předej klíč jako první argument." >&2
    exit 1
  fi
fi

if [[ ${#KEY} -lt 32 ]]; then
  echo "ERROR: teacher key musí mít alespoň 32 znaků." >&2
  exit 1
fi

DIR="$(dirname "$TARGET")"
mkdir -p "$DIR"
TMP="$(mktemp)"
trap 'rm -f "$TMP"' EXIT

cat > "$TMP" <<PHP
<?php
return [
    'teacher_export_key' => $(php -r 'echo var_export($argv[1], true);' "$KEY"),
    'teacher_name' => $(php -r 'echo var_export($argv[1], true);' "$TEACHER_NAME"),
    'teacher_team_id' => $(php -r 'echo var_export($argv[1], true);' "$TEACHER_TEAM_ID"),
    'teacher_team_name' => $(php -r 'echo var_export($argv[1], true);' "$TEACHER_TEAM_NAME"),
    'teacher_role' => $(php -r 'echo var_export($argv[1], true);' "$TEACHER_ROLE"),
    'tutor_endpoint' => '',
    'tutor_token' => '',
    'tutor_model' => '',
];
PHP

install -m 640 "$TMP" "$TARGET"

# Match the application's owner/group so PHP-FPM can read the secret without
# making it world-readable. This works for ISPConfig and typical aaPanel setups.
APP_OWNER="$(stat -c '%U' "$ROOT" 2>/dev/null || true)"
APP_GROUP="$(stat -c '%G' "$ROOT" 2>/dev/null || true)"
if [[ -n "$APP_OWNER" && -n "$APP_GROUP" && "$APP_OWNER" != "UNKNOWN" && "$APP_GROUP" != "UNKNOWN" ]]; then
  chown "$APP_OWNER:$APP_GROUP" "$TARGET" 2>/dev/null || true
fi
chmod 640 "$TARGET"

cat <<EOF
OK: secret vytvořen: $TARGET
Teacher: $TEACHER_NAME
Teacher team: $TEACHER_TEAM_NAME ($TEACHER_TEAM_ID)
Teacher role: $TEACHER_ROLE
Teacher key: $KEY

Ulož si klíč do password manageru. Aplikace jej z bezpečnostních důvodů znovu nezobrazí.
Kontrola instalace: php tools/check_install.php
EOF
