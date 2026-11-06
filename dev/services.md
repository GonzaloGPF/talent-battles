# Services
This project does integrations with following services:
- [Mailtrap](https://mailtrap.io/)
- [Stripe](https://stripe.com/)
- [Google Tag Manager](https://tagmanager.google.com/)

Find out your testing keys from that services. You will need to create an account on each service if you need use them.

## Stripe Webhooks
You can test [Stripe Webhooks](https://stripe.com/docs/cli/docker) locally using Docker by issuing:

`docker run --network container:talentbattles-talentbattles.test-1 --rm -it stripe/stripe-cli listen --api-key <your-test-api-key> --forward-to http://talentbattles.test/stripe/webhook`

> Remember to set STRIPE_WEBHOOK_SECRET env variable with the webhook signing secret value
