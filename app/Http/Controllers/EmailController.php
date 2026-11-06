<?php

namespace App\Http\Controllers;

use App\Enums\Roles;
use App\Http\Requests\ContactEmailRequest;
use App\Http\Resources\EmailResource;
use App\Jobs\SendEmail;
use App\Models\Email;
use App\Models\User;
use App\Services\EmailService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Mail\Mailable;

class EmailController extends Controller
{
    public function __construct(public EmailService $emailService)
    {
        parent::__construct();
    }

    /**
     * Display a listing of the Email.
     */
    public function index(): JsonResponse
    {
        $emails = Email::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(EmailResource::collection($emails));
    }

    /**
     * Display the specified resource.
     */
    public function show(Email $email): JsonResponse
    {
        $this->loadRelations($email);

        return $this->showResponse(new EmailResource($email));
    }

    public function render(Email $email): Mailable|JsonResponse|null
    {
        try {
            return $this->emailService->getEmail($email);
        } catch (Exception $exception) {
            return $this->exceptionResponse($exception);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function contact(ContactEmailRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $userEmail = $user?->email ?? $request->get('email');

        $email = $this->emailService->createContactEmail(
            $userEmail,
            $request->get('message'),
            [config('mail.contact_email')]
        );

        return $this->storeResponse(new EmailResource($email), 'sent');
    }

    /**
     * @throws AuthorizationException
     */
    public function resend(Email $email): JsonResponse
    {
        if (! hasRole(Roles::ADMIN) && $email->retries >= 3) {
            throw new AuthorizationException(tl('too_many_retries'));
        }

        SendEmail::dispatch($email);

        $email->increment('retries');

        return $this->updateResponse(new EmailResource($email));
    }
}
