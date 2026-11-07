#!/usr/bin/env bash
#
# Runs on the server: goes back to the release before the current one.
# Database migrations are not reverted, the previous release must work with the current schema.
#
# Usage: bash ~/htdocs/talentbattles.com/current/deploy/rollback.sh
set -euo pipefail

root="$(dirname "$(readlink -f "${BASH_SOURCE[0]}")")/../../.."
root="$(cd "$root" && pwd)"
php="${PHP_BIN:-php8.4}"
failed=0

env_value() {
    sed -n "s/^$1=//p" "$root/shared/.env" | tail -n 1 | tr -d "\"'\r"
}

live="$(basename "$(readlink -f "$root/current")")"
target="$(find "$root/releases" -mindepth 1 -maxdepth 1 -type d -printf '%f\n' | sort | grep -B1 -Fx "$live" | head -n 1)"

if [ -z "$target" ] || [ "$target" = "$live" ]; then
    echo "There is no release before $live" >&2
    exit 1
fi

ln -sfn "$root/releases/$target" "$root/current.tmp"
mv -T "$root/current.tmp" "$root/current"
echo "Rolled back from $live to $target"

# Horizon and Reverb keep the old code in memory. They are only restarted when they are in use:
# Horizon needs Redis, and the server does not have to run it.
if [ "$(env_value QUEUE_CONNECTION)" = redis ]; then
    "$php" "$root/current/artisan" horizon:terminate || failed=1
fi

if [ "$(env_value BROADCAST_DRIVER)" = reverb ]; then
    "$php" "$root/current/artisan" reverb:restart || failed=1
fi

if [ "$failed" -eq 1 ]; then
    echo "The rollback is done, but the processes above could not be restarted" >&2
    exit 1
fi
