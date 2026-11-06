<?php

namespace App\Jobs;

use App\Facades\Logger;
use App\Models\Email;
use App\Services\EmailService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Email $email)
    {
        auth('web')->loginUsingId($this->email->created_by);
    }

    /**
     * Execute the job.
     */
    public function handle(EmailService $emailService): void
    {
        try {
            Mail::to($this->email->to)
                ->locale($this->email->createdBy?->locale)
//                ->subject($emailService->getSubject($this->email->email_type))
                ->send($emailService->getEmail($this->email));

            $this->email
                ->forceFill(['sent' => now()])
                ->save();
        } catch (Exception $exception) {
            Logger::exception($exception);
        }
    }
}
