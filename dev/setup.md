# Setup
Following documentation describes how setup project locally.

## Repository
Repository is in [GitHub](https://github.com/GonzaloGPF/TalentBattles)
Clone the repository with `git clone git@github.com:GonzaloGPF/TalentBattles.git`

> You will need to add [SSH](https://github.com/settings/keys) key in your GitHub account

## Env Variables
Create **.env** file. You can use **.env.example** file as a boilerplate.

You will need to set up the following *.env* variables:
- MAIL_USERNAME=
- MAIL_PASSWORD=
- STRIPE_KEY=
- STRIPE_SECRET=
- REDIS_HOST=redis
- REDIS_PASSWORD=null
- REDIS_PORT=6379

## Local Development
Local development is configured to use [Laravel Sail](https://laravel.com/docs/13.x/sail), it means you should not
install anything in your machine.

You only need to install [Docker](https://www.docker.com/).

To set up the project for the first time you will need to install dependencies without having installed Laravel Sail.
For that, you can use following command:

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs
```

After that, you normally will use `vendor/bin/sail up -d` and `vendor/bin/sail down`.

You can create an alias to simplify the command above.

```bash
alias sail='[ -f sail ] && sh sail || sh vendor/bin/sail'
```

By this way, your daily usage will remain with commands `sail up -d` and `sail down`

If you need to remove everything (volumes, images and networks) from docker you can use:

```bash
docker-compose down --rmi all
```

## Server Requirements
Your development environment will need the following extra software:

- All Laravel [requirements](https://laravel.com/docs/13.x/deployment#server-requirements)
- [AudioWaveForm](https://github.com/bbc/audiowaveform):
    - `sudo add-apt-repository ppa:chris-needham/ppa`
    - `sudo apt-get update`
    - `sudo apt-get install -y audiowaveform`
- [FFMPEG](http://www.ffmpeg.org/)
    - `sudo apt update`
    - `sudo apt install -y ffmpeg`
- [BPM-Tools](https://manpages.ubuntu.com/manpages/trusty/man1/bpm.1.html)
  - `sudo apt-get update`
  - `sudo apt-get install -y bpm-tools libsox-fmt-all id3v2`
- [Soundstretch](https://installati.one/install-soundstretch-ubuntu-20-04/)
  - `sudo apt-get update`
  - `sudo apt-get install -y soundstretch`
  
> If using sail, run `sail build` and remember to restart service 

## Installation
This project is ready for local development using Laravel Sail, it means you will need Docker installed.
You can check out Docker configuration at **docker-compose.yml** file.

As first step, you will need to run following commands
> Remember to init Docker before running commands

- `sail php artisan key:generate`
- `sail php artisan migrate --seed`
- `sail php artisan horizon:publish`
- `sail yarn`

## Daily Usage
Daily usage will require you to type following commands:
- Init web server with `sail up -d`
- Compile front with `sail yarn dev`

> This project uses yarn instead of npm 

### Alias
You can automatize commands with an alias

`alias sail-dev='sail up -d && sail yarn dev'`

By this way, your daily usage will be only `sail-dev` command

## Database
To rebuild database
`php artisan migrate:fresh`

To seed database will fake data
`php artisan db:seed`

You also can rebuild and seed database at the same time with
`php artisan migrate:fresh --seed`

## Websockets
Realtime is powered by [Laravel Reverb](https://laravel.com/docs/13.x/reverb) and it is disabled by default.
It can be activated by setting BROADCAST_DRIVER as `reverb`, filling the `REVERB_*` variables and compiling the front
again. To initialize websockets you should run:

`php artisan reverb:start`

## Background process
Background processes are managed by Laravel queue system. 
If you set QUEUE_CONNECTION variable as `redis` you should init queue service with:

`php artisan horizon`

## Webhooks
Use Ngrok for external webhooks in local development

`ngrok http 192.168.10.10:80 -host-header=talentbattles.test`

## Troubleshoot
Sometimes **.env** fails with error `./.env: line 71: $'\r': command not found`,
to fix that you can `sed -i 's/\r$//' .env`