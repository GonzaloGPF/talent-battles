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

live="$(basename "$(readlink -f "$root/current")")"
target="$(find "$root/releases" -mindepth 1 -maxdepth 1 -type d -printf '%f\n' | sort | grep -B1 -Fx "$live" | head -n 1)"

if [ -z "$target" ] || [ "$target" = "$live" ]; then
    echo "There is no release before $live" >&2
    exit 1
fi

ln -sfn "$root/releases/$target" "$root/current.tmp"
mv -T "$root/current.tmp" "$root/current"
"$php" "$root/current/artisan" horizon:terminate

echo "Rolled back from $live to $target"
