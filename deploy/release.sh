#!/usr/bin/env bash
#
# Runs on the server: unpacks an uploaded release and activates it atomically.
#
#   $DEPLOY_PATH/releases/<id>   every release, the latest ones are kept to roll back
#   $DEPLOY_PATH/shared          .env and storage, they survive between releases
#   $DEPLOY_PATH/current         symlink to the live release, the web server points here
#
# Anything that fails before the symlink changes leaves the live release untouched. If the site does
# not answer after the switch, it goes back to the previous release. PHP-FPM is not reloaded: Nginx
# gives it the real path of the release, and reloading would drop the requests in progress.
set -euo pipefail

: "${DEPLOY_PATH:?}" "${RELEASE:?}"

php="${PHP_BIN:-php8.4}"
keep="${KEEP_RELEASES:-5}"

releases="$DEPLOY_PATH/releases"
shared="$DEPLOY_PATH/shared"
current="$DEPLOY_PATH/current"
release_dir="$releases/$RELEASE"
archive="$release_dir.tar.gz"
previous=""
active=0

if [ -L "$current" ]; then
    previous="$(readlink -f "$current")"
fi

log() { printf '\n==> %s\n' "$*"; }

cleanup() {
    rm -f "$archive"

    if [ "$active" -eq 0 ]; then
        rm -rf "$release_dir"
    fi
}
trap cleanup EXIT

activate() {
    ln -sfn "$1" "$current.tmp"
    mv -T "$current.tmp" "$current"
    "$php" "$current/artisan" horizon:terminate

    if grep -q '^BROADCAST_DRIVER=reverb' "$shared/.env"; then
        "$php" "$current/artisan" reverb:restart
    fi
}

is_healthy() {
    local url
    url="$(sed -n 's/^APP_URL=//p' "$shared/.env" | tr -d "\"'")"

    for path in / /api/company /docs/es/conditions; do
        curl --fail --silent --show-error --output /dev/null --max-time 15 \
            --retry 4 --retry-delay 3 --retry-connrefused "$url$path" || return 1
    done
}

log "Checking the server"
[ -f "$shared/.env" ] || { echo "Missing $shared/.env" >&2; exit 1; }
[ -f "$archive" ] || { echo "Missing $archive" >&2; exit 1; }
command -v rsync > /dev/null || { echo "rsync is not installed" >&2; exit 1; }

log "Unpacking release $RELEASE"
mkdir -p "$release_dir" "$shared/storage"
tar -xzf "$archive" -C "$release_dir"
rsync -a --ignore-existing "$release_dir/storage/" "$shared/storage/"
rm -rf "$release_dir/storage"
ln -s "$shared/storage" "$release_dir/storage"
ln -s "$shared/.env" "$release_dir/.env"

cd "$release_dir"

log "Preparing the release"
"$php" artisan storage:link --force
"$php" artisan vendor:publish --tag=larecipe-assets --force
"$php" artisan migrate --force
"$php" artisan optimize

log "Activating the release"
activate "$release_dir"
active=1

log "Checking the site"
if ! is_healthy; then
    if [ -n "$previous" ]; then
        echo "The site is not answering: going back to $(basename "$previous")" >&2
        activate "$previous"
        active=0
    fi

    exit 1
fi

"$php" artisan notify:new_deploy

log "Removing old releases"
find "$releases" -mindepth 1 -maxdepth 1 -type d -printf '%f\n' | sort -r | tail -n "+$((keep + 1))" |
    while read -r old; do
        [ "$releases/$old" = "$release_dir" ] || [ "$releases/$old" = "$previous" ] || rm -rf "${releases:?}/$old"
    done

log "Release $RELEASE is live"
