<?php

namespace App\Services;

use App\Events\InvitationCreated;
use App\Facades\Logger;
use App\Models\Invitation;
use App\Models\User;
use Exception;
use Illuminate\Support\Collection;

class InvitationService
{
    public function createMultiple(array $data): Collection
    {
        return collect($data)
            ->map(fn (array $invitationData) => $this->create($invitationData))
            ->filter();
    }

    public function create(array $invitationData): ?Invitation
    {
        try {
            $invitation = Invitation::forceCreate([
                'created_by' => auth()->id(),
                'name' => $invitationData['name'] ?? null,
                'email' => $invitationData['email'] ?? null,
            ]);

            InvitationCreated::dispatch($invitation);

            return $invitation;
        } catch (Exception $exception) {
            Logger::exception($exception);

            return null;
        }
    }

    public function associateToUser(User $user): void
    {
        Invitation::whereNull('user_id')
            ->where('email', $user->email)
            ->update(['user_id' => $user->id]);

        $user->forceFill(['verified_at' => now()])
            ->save();
    }
}
