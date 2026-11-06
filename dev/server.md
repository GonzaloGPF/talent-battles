# Server
This project runs in a VPS at [Hostinger](https://www.hostinger.com/vps-hosting) with [CloudPanel](https://www.cloudpanel.io/),
next to other sites. Each site has its own user, PHP version and Nginx configuration, so nothing in this guide should
change global settings: the default `php` command, or PHP-FPM pools of other sites.

## Deployment
Every push to `main` runs [ci.yml](../.github/workflows/ci.yml):

1. **Quality and tests**: Pint, Larastan and PHPUnit.
2. **Package**: [package.sh](../deploy/package.sh) builds an archive with the code, production dependencies and compiled assets.
3. **Deploy**: [deploy.sh](../deploy/deploy.sh) uploads it and runs [release.sh](../deploy/release.sh) in the server.

The server only needs PHP: it does not need Composer, Node.js or access to the repository.

Releases are atomic. They are prepared in their own folder and `current` is switched at the very end, so a failing step
leaves the live release untouched. If the site does not answer after the switch, it goes back to the previous release.

```
/home/<site-user>/htdocs/talentbattles.com
├── current -> releases/20261106170000-a1b2c3d   Live release, the site root points here
├── releases                                     Last 5 releases
└── shared
    ├── .env
    └── storage
```

### Manual deployment
From any machine with SSH access to the server:

```bash
VITE_STRIPE_KEY=pk_live_... deploy/package.sh
```

```bash
DEPLOY_HOST=talentbattles.com DEPLOY_USER=<site-user> DEPLOY_PATH=/home/<site-user>/htdocs/talentbattles.com deploy/deploy.sh release.tar.gz
```

### Rollback
Go back to the previous release. Migrations are not reverted, so it must work with the current database schema.

```bash
ssh <site-user>@talentbattles.com bash htdocs/talentbattles.com/current/deploy/rollback.sh
```

### Updating by hand
Deployment from GitHub Actions is off, so this is how the site is updated now. It runs with the `talentbattles` user,
in `~/htdocs/talentbattles.com`. It needs Node.js 24, installed with nvm for this user only.

```bash
git pull origin main
php8.4 "$(which composer)" install --no-dev --optimize-autoloader
php8.4 artisan migrate --force
yarn install --frozen-lockfile --ignore-scripts && yarn build
php8.4 artisan vendor:publish --tag=larecipe-assets --force
php8.4 artisan optimize
```

Redis is not used: cache and sessions use files and the queue is `sync`, so there is no Horizon to restart.

## Server setup

### Site settings
In the site page of CloudPanel:

1. **PHP Settings**: choose PHP 8.4, with `post_max_size` and `upload_max_filesize` set to `100M`.
2. **Vhost**: replace `$document_root` with `$realpath_root` in the PHP location. It is required: it makes PHP see the
   real path of each release, so the new code is live right after the switch, without reloading PHP-FPM.

   ```nginx
   fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
   ```

### Software
As `root`. The audio tools are used by the application, and they do not affect other sites.

```bash
apt-get install -y rsync supervisor redis-server ffmpeg bpm-tools libsox-fmt-all soundstretch id3v2
```

`audiowaveform` comes from a PPA on Ubuntu. On Debian, install the package from its [releases](https://github.com/bbc/audiowaveform/releases).

```bash
add-apt-repository -y ppa:chris-needham/ppa && apt-get update && apt-get install -y audiowaveform
```

### SSH key
Create a key only for deployments, and add its public part to `~/.ssh/authorized_keys` of the site user:

```bash
ssh-keygen -t ed25519 -f talentbattles-deploy -N '' -C 'github-actions-deploy'
```

### Horizon
This needs a first release in `current`, so it goes after the first deployment.

`/etc/supervisor/conf.d/talentbattles-horizon.conf`:

```ini
[program:talentbattles-horizon]
command=/usr/bin/php8.4 /home/<site-user>/htdocs/talentbattles.com/current/artisan horizon
user=<site-user>
autostart=true
autorestart=true
stopwaitsecs=3600
redirect_stderr=true
stdout_logfile=/home/<site-user>/htdocs/talentbattles.com/shared/storage/logs/horizon.log
```

```bash
supervisorctl reread && supervisorctl update
```

### Scheduler
In the **Cron Jobs** section of the site, every minute:

```
/usr/bin/php8.4 /home/<site-user>/htdocs/talentbattles.com/current/artisan schedule:run
```

## GitHub configuration
Deployment is off until the `DEPLOY_ENABLED` variable is `true`.

Create an environment named `production` in the repository settings, limited to the `main` branch, with these secrets:

- `DEPLOY_SSH_KEY`: private key created above
- `DEPLOY_KNOWN_HOSTS`: output of `ssh-keyscan <server>`, checked against the server fingerprint
- `DEPLOY_HOST`, `DEPLOY_USER` (the site user) and `DEPLOY_PATH` (absolute, without `~`)
- `DEPLOY_PORT`: only if SSH does not use port 22

These are repository variables. `.env` does not exist while packaging, so `VITE_` values come from here:

- `DEPLOY_ENABLED`: `true`
- `VITE_STRIPE_KEY`: required, packaging fails without it
- `VITE_BROADCAST_DRIVER`, `VITE_REVERB_APP_KEY`, `VITE_REVERB_HOST`, `VITE_REVERB_PORT` and `VITE_REVERB_SCHEME`: only if realtime is enabled

## Moving a running site to this layout
The old site keeps working until its root changes, and going back only takes restoring that field.

1. Back up the database.
2. As the site user, inside `~/htdocs/talentbattles.com`, create `releases` and `shared`, copy the old `.env` to `shared/.env`
   and the old `storage` folder to `shared/storage`.
3. Change the [Vhost](#site-settings) to `$realpath_root`, and check that `php8.4 -v` works. Deployments use that command, so the old site can keep its PHP version for now.
4. Set `DEPLOY_ENABLED` to `true` and run the first deployment. It runs migrations, but the old site stays live.
5. In the site settings, choose PHP 8.4 and change **Root Directory** to `current/public`.
6. Stop the old Horizon daemon and the old cron, and set up [Horizon](#horizon) and the [scheduler](#scheduler) as shown above.

## Realtime
It is disabled in production. To enable it, set `BROADCAST_DRIVER=reverb` and the `REVERB_*` variables, add
`php artisan reverb:start` to supervisor and set the repository variables above.
