<?php

namespace App\Services;

use App\Enums\EmailTypes;
use App\Facades\Logger;
use App\Jobs\SendEmail;
use App\Mail\ContactEmail;
use App\Mail\CreationSoldEmail;
use App\Mail\InvitationEmail;
use App\Mail\OrderSuccessEmail;
use App\Mail\ResetPasswordEmail;
use App\Mail\VerifyEmail;
use App\Models\Email;
use App\Models\Invitation;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Sale;
use App\Models\User;
use BackedEnum;
use Exception;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Collection;

class EmailService
{
    public function createOrderSuccess(Order $order): Email
    {
        return $this->create(EmailTypes::ORDER_SUCCESS, [$order->email], [
            'order_id' => $order->id,
            'user_name' => $order->createdBy?->getNickName(),
            'lines' => $order->orderLines
                ->map(fn (OrderLine $orderLine) => [
                    'description' => $orderLine->description,
                    'url' => $orderLine->getFileUrl(),
                ])->toArray(),
        ]);
    }

    public function createVerifyEmail(User $user): ?Email
    {
        return $this->create(EmailTypes::VERIFY_EMAIL, [$user->email], [
            'user_name' => $user->getNickName(),
            'email' => $user->email,
            'url' => route('users.verify', ['user' => $user->token]),
        ]);
    }

    public function createPasswordReset(User $user, bool $isNew = false): ?Email
    {
        return $this->create(EmailTypes::RESET_PASSWORD, [$user->email], [
            'user_name' => $user->getNickName(),
            'email' => $user->email,
            'is_new' => $isNew,
            'url' => str_replace('api/', '', route('auth.password.reset', ['token' => $user->token])),
        ]);
    }

    public function createCreationSold(Collection $sales, User $user): ?Email
    {
        return $this->create(EmailTypes::CREATION_SOLD, [$user->email], [
            'user_name' => $user->getNickName(),
            'lines' => collect($sales)
                ->map(fn (Sale $sale) => [
                    'description' => $sale->description,
                    'amount' => $sale->amount,
                    'commission_amount' => $sale->commission_amount,
                    'total' => $sale->total,
                ])->toArray(),
        ]);
    }

    /**
     * @param  string  $email
     * @param  string  $creatorName
     * @param  string|null  $userName
     */
    public function createInvitationEmail(Invitation $invitation): ?Email
    {
        return $this->create(EmailTypes::INVITATION_EMAIL, [$invitation->email], [
            'invitation_id' => $invitation->id,
        ]);
    }

    public function createContactEmail(string $email, string $message, array $emails): ?Email
    {
        return $this->create(EmailTypes::CONTACT_EMAIL, $emails, [
            'email' => $email,
            'message' => $message,
        ]);
    }

    public function getEmail(Email $email): ?Mailable
    {
        try {
            return match ($email->email_type) {
                EmailTypes::VERIFY_EMAIL => $this->verifyEmail($email),
                EmailTypes::RESET_PASSWORD => $this->resetPassword($email),
                EmailTypes::ORDER_SUCCESS => $this->orderSuccess($email),
                EmailTypes::CREATION_SOLD => $this->creationSold($email),
                EmailTypes::CONTACT_EMAIL => $this->contactEmail($email),
                EmailTypes::INVITATION_EMAIL => $this->invitationEmail($email),
            };
        } catch (Exception $exception) {
            Logger::exception($exception);

            return null;
        }
    }

    public function verifyEmail(Email $email): VerifyEmail
    {
        return new VerifyEmail($email->meta['user_name'], $email->meta['email'], $email->meta['url']);
    }

    public function resetPassword(Email $email): ResetPasswordEmail
    {
        return new ResetPasswordEmail($email->meta['user_name'], $email->meta['url'], $email->meta['is_new'] ?? false);
    }

    public function orderSuccess(Email $email): OrderSuccessEmail
    {
        return new OrderSuccessEmail($email->meta['lines'], $email->meta['user_name']);
    }

    private function creationSold(Email $email): CreationSoldEmail
    {
        return new CreationSoldEmail($email->meta['user_name'], $email->meta['lines']);
    }

    private function contactEmail(Email $email): ContactEmail
    {
        return new ContactEmail($email->meta['email'], $email->meta['message']);
    }

    private function invitationEmail(Email $email): InvitationEmail
    {
        return new InvitationEmail(Invitation::find($email->meta['invitation_id']));
    }

    private function create(BackedEnum $emailType, array $to, ?array $meta = []): ?Email
    {
        if (empty($to)) {
            return null;
        }

        $email = Email::forceCreate([
            'created_by' => auth()->id(),
            'email_type' => $emailType,
            'to' => $to,
            'meta' => $meta,
        ]);

        SendEmail::dispatch($email);

        return $email;
    }
}
