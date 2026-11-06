#!/usr/bin/env bash
#
# Uploads a release archive to the server and activates it. GitHub Actions uses it on every push to
# main, and it can also be run by hand from any machine with SSH access to the server.
#
# Usage: DEPLOY_HOST=... DEPLOY_USER=... DEPLOY_PATH=... deploy/deploy.sh <archive> [release-id]
set -euo pipefail

: "${DEPLOY_HOST:?}" "${DEPLOY_USER:?}" "${DEPLOY_PATH:?}"

archive="${1:?Usage: deploy.sh <archive> [release-id]}"
release="${2:-$(date -u +%Y%m%d%H%M%S)}"
port="${DEPLOY_PORT:-22}"
target="$DEPLOY_USER@$DEPLOY_HOST"
options=(-o BatchMode=yes)

ssh "${options[@]}" -p "$port" "$target" "mkdir -p '$DEPLOY_PATH/releases'"
scp "${options[@]}" -P "$port" "$archive" "$target:$DEPLOY_PATH/releases/$release.tar.gz"
ssh "${options[@]}" -p "$port" "$target" \
    "DEPLOY_PATH='$DEPLOY_PATH' RELEASE='$release' bash -s" \
    < "$(dirname "${BASH_SOURCE[0]}")/release.sh"
