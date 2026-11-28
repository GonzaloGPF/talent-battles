<?php

namespace App\Api;

use App\Facades\Logger;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Sale;
use App\Models\User;
use App\Objects\StripeLineItem;
use App\Responses\SimpleResponse;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Payment;
use Laravel\Cashier\PaymentMethod;
use Stripe\Account;
use Stripe\AccountLink;
use Stripe\Checkout\Session;
use Stripe\Customer;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\InvalidRequestException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Transfer;

class StripeApi extends Api
{
    /**
     * @var array<string, string>
     */
    private static array $API = [
        'accounts.index' => 'v1/accounts',
        'accounts.show' => 'v1/accounts/{id}',
        'accounts.destroy' => 'v1/accounts/{id}',
    ];

    const RETURN_PATH = 'profile/account';

    const REFRESH_PATH = 'profile/account';

    const OK_PAYMENT_STATUS = 'succeeded';

    const FAILED_PAYMENT_STATUS = 'requires_payment_method';

    private ?User $user;

    private string $currency;

    public function __construct(SimpleResponse $response)
    {
        parent::__construct($response);
        $this->user = auth()->user();
        $this->currency = config('company.currency');

        $token = config('services.stripe.secret');

        $this->setEnabled(config('services.stripe.enabled'));
        $this->setOptions([
            'base_uri' => config('services.stripe.api'),
        ]);
        $this->setToken($token);

        Stripe::setApiKey($token);
    }

    public function createOrGetStripeCustomer(?User $user = null): ?Customer
    {
        $user = $user ?? $this->user;

        if (! $this->isEnabled() || ! $user) {
            return null;
        }

        try {
            return $user->createOrGetStripeCustomer([
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->toStripeAddress(),
                'expand' => ['tax'],
                //                'tax' => ['ip_address' => '1.1.1.1'],
            ]);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return null;
        }
    }

    /**
     * @return Collection<int, PaymentMethod>
     */
    public function getPaymentMethods(?User $user = null): Collection
    {
        $user = $user ?? $this->user;

        if (! $this->isEnabled()) {
            return collect();
        }

        try {
            return $user->paymentMethods('card');
        } catch (Exception $exception) {
            return collect();
        }
    }

    /**
     * A legacy default source (a card or bank account saved before payment methods) does not count.
     */
    public function getDefaultPaymentMethod(?User $user = null): ?PaymentMethod
    {
        $user = $user ?? $this->user;

        if (! $this->isEnabled()) {
            return null;
        }

        try {
            $paymentMethod = $user?->defaultPaymentMethod();
        } catch (Exception $exception) {
            return null;
        }

        return $paymentMethod instanceof PaymentMethod ? $paymentMethod : null;
    }

    public function setDefaultPaymentMethod(string $paymentMethodId, ?User $user = null): ?PaymentMethod
    {
        $user = $user ?? $this->user;

        if (! $this->isEnabled()) {
            return null;
        }

        try {
            return $user->updateDefaultPaymentMethod($paymentMethodId);
        } catch (Exception $exception) {
            return null;
        }
    }

    public function addPaymentMethod(string $paymentMethodId, ?User $user = null): ?PaymentMethod
    {
        $user = $user ?? $this->user;

        if (! $this->isEnabled()) {
            return null;
        }

        try {
            return $user->addPaymentMethod($paymentMethodId);
        } catch (Exception $exception) {
            return null;
        }
    }

    public function deletePaymentMethod(string $paymentMethodId, ?User $user = null): void
    {
        $user = $user ?? $this->user;

        if (! $this->isEnabled()) {
            return;
        }

        if (! $user->hasStripeId()) {
            $this->createOrGetStripeCustomer($user);
        }

        try {
            if ($paymentMethodId === '0') {
                $user->deletePaymentMethods('card');
            } else {
                $user->deletePaymentMethod($paymentMethodId);
            }
        } catch (Exception $exception) {
            Logger::exception($exception);
        }
    }

    /**
     * @return Collection<array-key, mixed>
     */
    public function getAccounts(): Collection
    {
        if (! $this->isEnabled()) {
            return collect();
        }

        try {
            return collect(Account::all()->toArray());
        } catch (Exception $exception) {
            Logger::exception($exception);

            return collect();
        }
    }

    /**
     * @return array<mixed>|null
     */
    public function destroyAccount(?User $user = null): ?array
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $user = $user ?? $this->user;

        $url = str_replace('{id}', $user->account['id'], self::$API['accounts.destroy']);

        try {
            return $this->delete($url);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return null;
        }
    }

    public function getAccount(User $user): ?Account
    {
        if (! $this->isEnabled()) {
            return null;
        }

        try {
            return Account::retrieve($user->account['id'] ?? null);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return null;
        }
    }

    /**
     * @throws ValidationException
     */
    public function createAccount(User $user): ?Account
    {
        if (! $this->isEnabled()) {
            return null;
        }

        try {
            return Account::create([
                'country' => Str::upper($user->address['country_code'] ?? null),
                'type' => 'express',
                'email' => $user->email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'business_type' => 'individual',
                'business_profile' => ['url' => route('users.show', $user->id)],
                'individual' => [
                    'email' => $user->email,
                    'first_name' => $user->name,
                    'last_name' => $user->last_name,
                    'phone' => $user->phone,
                    'gender' => strtolower($user->gender?->value),
                    'dob' => [
                        'day' => $user->birthdate?->day,
                        'month' => $user->birthdate?->month,
                        'year' => $user->birthdate?->year,
                    ],
                    'address' => $user->toStripeAddress(),
                ],
            ]);
        } catch (InvalidRequestException $exception) {
            throw ValidationException::withMessages([
                'stripe_errors' => [$exception->getMessage()],
            ]);
        } catch (ApiErrorException $exception) {
            Logger::exception($exception);

            return null;
        }
    }

    public function createAccountLink(?User $user = null): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $user = $user ?? $this->user;

        try {
            return AccountLink::create([
                'account' => $user->account['id'] ?? null,
                'refresh_url' => URL::to(self::REFRESH_PATH),
                'return_url' => URL::to(self::RETURN_PATH),
                'type' => 'account_onboarding',
            ])->url;
        } catch (Exception $exception) {
            Logger::exception($exception);

            return null;
        }
    }

    /**
     * Valid for logged and not logged users
     *
     * @param  array{payment_method?: string|null, confirmation_method?: string, automatic_payment_methods?: null, payment_method_types?: array<int, string>, confirm?: bool}  $options
     */
    public function createPaymentIntent(float $amount, string $email, ?User $user = null, array $options = []): ?Payment
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $user = $user ?? $this->user;

        $customer = $this->createOrGetStripeCustomer($user);

        try {
            $paymentIntent = PaymentIntent::create(array_filter([
                'amount' => $this->getStripeAmount($amount),
                'setup_future_usage' => empty($customer) ? null : 'on_session', // 'off_session',
                'currency' => $user->currency ?? $this->currency,
                'customer' => $customer?->id,
                //                'payment_method_types' => ['card'],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
                //                'application_fee_amount' => config('company.fees.creation'), // Can only apply an application_fee_amount (using an OAuth key or Stripe-Account header) or destination payment (using `transfer_data[destination]`)
                'receipt_email' => $email,
                ...$options,
            ], fn ($value) => $value !== null));

            return new Payment($paymentIntent);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return null;
        }
    }

    /**
     * Valid only for logged users
     */
    public function createConfirmedPaymentIntent(float $amount): ?Payment
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $paymentMethod = $this->getDefaultPaymentMethod();

        try {
            return $this->createPaymentIntent($amount, $this->user->email, $this->user, [
                'payment_method' => $paymentMethod?->asStripePaymentMethod()->id,
                'confirmation_method' => 'automatic',
                'automatic_payment_methods' => null, // Stripe would require a return_url otherwise
                'payment_method_types' => ['card'],
                'confirm' => true,
            ]);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return null;
        }
    }

    /**
     * @param  array<string, mixed>  $options
     */
    public function updatePaymentIntent(PaymentIntent $paymentIntent, float $amount, array $options = []): ?PaymentIntent
    {
        if (! $this->isEnabled()) {
            return null;
        }

        try {
            return PaymentIntent::update($paymentIntent->id, array_merge([
                'amount' => $this->getStripeAmount($amount),
            ], $options));
        } catch (Exception $exception) {
            Logger::exception($exception);

            return null;
        }
    }

    public function createOrderSession(Order $order, string $successUrl): ?string // TODO: not used
    {
        if (! $this->isEnabled()) {
            return null;
        }

        try {
            return Session::create([
                'payment_method_types' => ['card'],
                'customer_email' => $order->email,
                'line_items' => $order->orderLines
                    ->map(fn (OrderLine $orderLine) => $orderLine->toLineItem())
                    ->map(fn (StripeLineItem $lineItem) => $lineItem->toArray())
                    ->toArray(),
                'mode' => 'payment',
                'success_url' => $successUrl,
                'cancel_url' => config('app.url'),
            ])->url;
        } catch (Exception $exception) {
            Logger::exception($exception);

            return null;
        }
    }

    /**
     * Sends the seller's part of a sale to their connected account. Tied to the charge that paid the order, the
     * transfer can be made before that money is available, and the idempotency key stops it from being sent twice.
     */
    public function transferToSeller(Sale $sale, string $accountId, string $chargeId): ?Transfer
    {
        if (! $this->isEnabled()) {
            return null;
        }

        try {
            return Transfer::create([
                'amount' => $this->getStripeAmount($sale->total),
                'currency' => $this->currency,
                'destination' => $accountId,
                'source_transaction' => $chargeId,
                'description' => $sale->description,
                'metadata' => ['sale_id' => (string) $sale->id],
            ], ['idempotency_key' => "sale_{$sale->id}_transfer"]);
        } catch (Exception $exception) {
            Logger::exception($exception, ['sale' => $sale->id]);

            return null;
        }
    }

    /**
     * The charge that paid a payment intent, which funds the transfers to the sellers.
     */
    public function getChargeId(string $paymentIntentId): ?string
    {
        $charge = $this->retrievePaymentIntent($paymentIntentId)?->latest_charge;

        return is_string($charge) ? $charge : $charge?->id;
    }

    public function retrievePaymentIntent(string $id): ?PaymentIntent
    {
        try {
            return Cashier::stripe()->paymentIntents->retrieve($id);
        } catch (Exception $exception) {
            Logger::exception($exception);

            return null;
        }
    }

    /**
     * @throws ApiErrorException
     */
    public function confirmPaymentIntent(string $paymentId, string $returnUrl): ?PaymentIntent
    {
        $payment = $this->retrievePaymentIntent($paymentId);

        return $payment?->confirm([
            'return_url' => $returnUrl,
        ]);
    }

    /**
     * The browser can reach the success page without paying, so only Stripe can tell if an order was paid:
     * the payment has to be charged, in the right currency, for at least the total of the order.
     */
    public function isOrderPaid(Order $order): bool
    {
        $paymentIntentId = $order->meta['payment_intent_id'] ?? null;

        if (! $this->isEnabled() || ! $paymentIntentId) {
            return false;
        }

        $paymentIntent = $this->retrievePaymentIntent($paymentIntentId);

        if ($paymentIntent?->status !== self::OK_PAYMENT_STATUS) {
            return false;
        }

        /** @var ?User $buyer */
        $buyer = $order->createdBy;

        $isFullyPaid = $paymentIntent->currency === strtolower($buyer->currency ?? $this->currency)
            && $paymentIntent->amount_received >= $this->getStripeAmount($order->total);

        if (! $isFullyPaid) {
            Logger::error('Stripe payment does not cover the order', [
                'order' => $order->id,
                'total' => $order->total,
                'payment_intent' => $paymentIntentId,
                'amount_received' => $paymentIntent->amount_received,
                'currency' => $paymentIntent->currency,
            ]);
        }

        return $isFullyPaid;
    }

    /**
     * After a failed attempt, Stripe keeps the error and waits for another payment method. A payment that has not been
     * tried yet waits too, but without an error.
     */
    public function hasPaymentFailed(string $paymentIntentId): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $paymentIntent = $this->retrievePaymentIntent($paymentIntentId);

        return $paymentIntent?->status === self::FAILED_PAYMENT_STATUS && isset($paymentIntent->last_payment_error);
    }

    public function getPaymentIntentId(Payment $payment): string
    {
        return $payment->asStripePaymentIntent()->id;
    }

    private function getStripeAmount(float $amount): int
    {
        return (int) round($amount * 100); // 19.99 * 100 is 1998.9999999999998 in floating point
    }
}
