<?php

namespace App\Http\Controllers;

use App\Models\Invitation;
use Illuminate\Contracts\View\View;

class InvitationClaimController extends Controller
{
    public function __invoke(Invitation $invitation): View
    {
        $invitationData = $invitation
            ->toArray();

        $invitationData['createdBy'] = [
            'name' => $invitation->createdBy->getNickName(),
        ];

        return view('app')->with(['invitation' => $invitationData]);
    }
}
