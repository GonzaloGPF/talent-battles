#!/usr/bin/env bash
#
# Builds the archive that gets deployed: the tracked code, production PHP dependencies and compiled
# assets. It works on a clean export of HEAD, so uncommitted changes and secrets never get into it.
#
# Usage: VITE_STRIPE_KEY=pk_... deploy/package.sh [output-archive]
set -euo pipefail

: "${VITE_STRIPE_KEY:?VITE_STRIPE_KEY is required: without it payments would not work in production}"

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
output="$(realpath -m "${1:-$root/release.tar.gz}")"
build="$(mktemp -d)"
trap 'rm -rf "$build"' EXIT
chmod 755 "$build" # tar stores this mode, and the web server has to get into the release

git -C "$root" archive HEAD | tar -x -C "$build"
cd "$build"

composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-progress
yarn install --frozen-lockfile --non-interactive --ignore-scripts
yarn build

tar --create --gzip --file "$output" --no-wildcards-match-slash \
    --exclude='./.git*' --exclude='./.claude' --exclude='./.editorconfig' --exclude='./.env*' \
    --exclude='./.husky' --exclude='./.mcp.json' --exclude='./.stylelintrc.json' \
    --exclude='./*.md' --exclude='./boost.json' --exclude='./docker' --exclude='./docker-compose.yml' \
    --exclude='./dev' --exclude='./eslint.config.js' --exclude='./jsconfig.json' \
    --exclude='./node_modules' --exclude='./package.json' --exclude='./phpstan*.neon' \
    --exclude='./phpunit.xml' --exclude='./postcss.config.js' --exclude='./tailwind.config.js' \
    --exclude='./tests' --exclude='./vite.config.js' --exclude='./yarn.lock' \
    --exclude='./database/backup-*.sql' --exclude='./resources/js' --exclude='./resources/sass' \
    --exclude='./lang/php_*.json' \
    .

echo "Release package: $output ($(du -h "$output" | cut -f1))"
