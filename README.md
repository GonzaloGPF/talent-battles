# Talent Battles

[![CI](https://github.com/GonzaloGPF/talent-battles/actions/workflows/ci.yml/badge.svg)](https://github.com/GonzaloGPF/talent-battles/actions/workflows/ci.yml)
![Laravel 13](https://img.shields.io/badge/Laravel-13-FF2D20?logo=laravel&logoColor=white)
![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4?logo=php&logoColor=white)
![Vue 3](https://img.shields.io/badge/Vue-3-4FC08D?logo=vuedotjs&logoColor=white)

A marketplace and competition platform for music producers and designers, built with Laravel and Vue.
Creators upload their work, face each other in community-voted battles, earn points and sell what they make.

**[talentbattles.com](https://talentbattles.com)**

<img alt="Talent Battles" src="./public/images/logo_big_bg.webp" width="600"/>

## Features

- **Creations**: beats, songs, samples and designs, organized in albums, with tags and filters.
- **Battles**: two creations face each other, the community votes, and the winner earns points.
- **Progression**: points, levels and promotions that give creators more visibility.
- **Marketplace**: cart, checkout, saved cards, exclusive sales and payouts to sellers through Stripe.
- **Community**: comments, likes, favorite lists, invitations, donations, reports and notifications.
- **Dashboards**: statistics for creators and a full admin panel.
- **Audio player**: playback with generated waveforms across the whole site.
- **Documentation**: a user guide in Spanish and English, including the legal pages.

## Technical highlights

**Audio and image processing.** Uploads go through queued jobs that read the duration and BPM, generate the waveform
with `audiowaveform`, add an audio watermark with FFmpeg and convert images to WebP.

**Payments where Stripe has the last word.** The browser cannot complete an order: the success page, the webhook and
saved cards all go through one method that asks Stripe, and checks status, currency and amount. A payment can only
complete one order, and a lock stops concurrent requests from paying a seller twice.

**Event-driven domain.** Finishing a battle or completing an order dispatches an event, and small listeners give
points, update ratings, record activity, send notifications and pay sellers.

**Battle state machine.** Battles move through explicit states, with a validator for each transition and hooks that
run after it, so a battle cannot finish twice or start without valid opponents.

**Test-driven.** Around 780 PHPUnit tests run against MySQL, using the real image driver and FFmpeg where it matters.
Stripe is tested by faking only the HTTP layer of its SDK, which checks the requests that are really sent.

**Automated quality and delivery.** CI runs Pint, Larastan and the test suite on every pull request. The `deploy`
folder holds a pipeline with atomic releases and automatic rollback, tested against a simulated server.

## Tech stack

| Area | Technologies |
| :- | :- |
| Backend | Laravel 13, PHP 8.4, MySQL, Sanctum, Socialite, Cashier |
| Frontend | Vue 3, Vuetify 3, Pinia, Vue Router, Tailwind CSS, Vite |
| Media | FFmpeg, audiowaveform, Intervention Image, AmplitudeJS, GSAP, Chart.js, D3 |
| Services | Stripe, Amazon S3, Amazon SES |
| Realtime and queues | Laravel Reverb and Horizon, both ready and off in production to keep the server simple |
| Quality | PHPUnit, Larastan, Pint, ESLint, Stylelint |
| Infrastructure | GitHub Actions, Laravel Sail, Nginx, CloudPanel on a VPS |
| Documentation | LaRecipe |

## Running it locally

You only need [Docker](https://www.docker.com/). The full guide is in [dev/setup.md](dev/setup.md).

```bash
cp .env.example .env
docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install --ignore-platform-reqs
vendor/bin/sail up -d
vendor/bin/sail artisan key:generate
vendor/bin/sail artisan migrate --seed
vendor/bin/sail yarn
vendor/bin/sail yarn dev
```

Run the tests with:

```bash
vendor/bin/sail artisan test
```

## Developer docs

- [Local setup](dev/setup.md)
- [Server and deployment](dev/server.md)
- [External services](dev/services.md)

---

Designed and built by [Gonzalo Pabón](https://github.com/GonzaloGPF).
