# Server
This project runs in a VPS at [Hostinger](https://www.hostinger.com/vps-hosting) with [CloudPanel](https://www.cloudpanel.io/),
next to other sites. Each site has its own user, PHP version and Nginx configuration, so nothing in this guide should
change global settings: the default `php` command, or PHP-FPM pools of other sites.

## Deployment
Every push to `main` runs [ci.yml](../.github/workflows/ci.yml):

1. **Quality and tests**: Pint, Larastan and PHPUnit.
2. **Package**: [package.sh](../deploy/package.sh) builds an archive with the code, production dependencies and compiled assets.
3. **Deploy**: [deploy.sh](../deploy/deploy.sh) uploads it and runs [release.sh](../deploy/release.sh) in the server.

To deploy `main` again without a push, use **Run workflow** in the Actions tab. It runs the tests too.

The server only needs PHP: it does not need Composer, Node.js or access to the repository.

Releases are atomic. They are prepared in their own folder and `current` is switched at the very end, so a failing step
leaves the live release untouched. After the switch, the home page, the API and the documentation are checked, and if one
of them fails, it goes back to the previous release. Any path answers 200 because of the fallback route, so each check
also looks for something only that page has. A redirect is a failure: `APP_URL` has to be the final URL, with `https`.
Horizon is only restarted when `QUEUE_CONNECTION=redis`, and Reverb when `BROADCAST_DRIVER=reverb`, so the server does
not need Redis until they are in use.

```
/home/<site-user>/htdocs/talentbattles.com
├── current -> releases/20261106170000-a1b2c3d   Live release, the site root points here
├── releases                                     Last 5 releases
└── shared
    ├── .env
    └── storage
```

### Manual deployment
Only if GitHub Actions cannot be used, from a machine that can connect by SSH as the site user:

```bash
VITE_STRIPE_KEY=pk_live_... deploy/package.sh
```

```bash
DEPLOY_HOST=talentbattles.com DEPLOY_USER=<site-user> DEPLOY_PATH=/home/<site-user>/htdocs/talentbattles.com deploy/deploy.sh release.tar.gz
```

### Rollback
Go back to the previous release. Migrations are not reverted, so it must work with the current database schema.
In the server, as the site user:

```bash
bash ~/htdocs/talentbattles.com/current/deploy/rollback.sh
```

The next deployment goes forward again, so revert or fix the commit in `main` first.

### Changing the configuration
The production `.env` is `shared/.env`, and every release links to it. The configuration is cached, so after changing it:

```bash
php8.4 ~/htdocs/talentbattles.com/current/artisan optimize
```

Redis is not used: cache and sessions use files and the queue is `sync`.

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
As `root`. Deployments need `rsync`, and the audio tools are used by the application. None of them affect other sites.

```bash
apt-get install -y rsync ffmpeg bpm-tools libsox-fmt-all soundstretch id3v2
```

`supervisor` and `redis-server` are only needed for [Horizon](#horizon).

`audiowaveform` comes from a PPA on Ubuntu. On Debian, install the package from its [releases](https://github.com/bbc/audiowaveform/releases).

```bash
add-apt-repository -y ppa:chris-needham/ppa && apt-get update && apt-get install -y audiowaveform
```

### SSH key
Create a key only for deployments:

```bash
ssh-keygen -t ed25519 -f talentbattles-deploy -N '' -C 'github-actions-deploy'
```

Add its public part to `~/.ssh/authorized_keys` of the site user, as a new line that starts with `restrict `. It disables
port forwarding and interactive sessions, which deployments do not need. If you connect as `root`, run this from your machine:

```bash
{ printf 'restrict '; cat talentbattles-deploy.pub; } | ssh root@<server> 'su - <site-user> -c "umask 077; mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys"'
```

### Horizon
Only needed when the queue uses Redis (`QUEUE_CONNECTION=redis`). It needs a first release in `current`.

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
Not set up in production: the tasks in [Kernel.php](../app/Console/Kernel.php) do nothing useful there, `horizon:snapshot`
needs Redis, and `CheckBattlesToFinish` is commented out. When a task is needed, add this in the **Cron Jobs** section
of the site, every minute:

```
/usr/bin/php8.4 /home/<site-user>/htdocs/talentbattles.com/current/artisan schedule:run
```

## GitHub configuration
Deployment is off until the `DEPLOY_ENABLED` variable is `true`.

Create an environment named `production` in the repository settings, limited to the `main` branch, with these secrets:

- `DEPLOY_SSH_KEY`: private key created above
- `DEPLOY_KNOWN_HOSTS`: output of `ssh-keyscan <server>`, checked against the server fingerprint
- `DEPLOY_HOST` (the server IP, so deployments do not depend on DNS), `DEPLOY_USER` (the site user) and `DEPLOY_PATH`
  (output of `pwd -P` in the site folder)
- `DEPLOY_PORT`: only if SSH does not use port 22

These are repository variables. `.env` does not exist while packaging, so `VITE_` values come from here:

- `DEPLOY_ENABLED`: `true`
- `VITE_STRIPE_KEY`: required, packaging fails without it
- `VITE_BROADCAST_DRIVER`, `VITE_REVERB_APP_KEY`, `VITE_REVERB_HOST`, `VITE_REVERB_PORT` and `VITE_REVERB_SCHEME`: only if realtime is enabled

## Moving a running site to this layout
The old site keeps working until its root changes, and going back only takes restoring that field.

1. Update the old site to `main` and back up the database, so the first deployment has no migrations to run.
2. As the site user, inside `~/htdocs/talentbattles.com`, create `releases` and `shared`, copy the old `.env` to `shared/.env`
   and the old `storage` folder to `shared/storage`. Check that `APP_URL` in `shared/.env` uses `https`.
3. Change the [Vhost](#site-settings) to `$realpath_root`, and check that `php8.4 -v` works. Deployments use that command, so the old site can keep its PHP version for now.
4. Set `DEPLOY_ENABLED` to `true` and run the first deployment. The old site stays live, so this health check tests the old site.
5. Copy `storage` again with `rsync -a --update storage/ shared/storage/`, to keep open sessions. In the site settings,
   choose PHP 8.4 and change **Root Directory** to `current/public`.
6. Stop the old Horizon daemon and the old cron if there are any, and set up [Horizon](#horizon) and the
   [scheduler](#scheduler) only if they are needed.

## Realtime
It is disabled in production. To enable it, set `BROADCAST_DRIVER=reverb` and the `REVERB_*` variables, add
`php artisan reverb:start` to supervisor and set the repository variables above.
