<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

/**
 * Class LocaleController
 *
 * @group Auth
 */
class LocaleController extends Controller
{
    public function update(): JsonResponse
    {
        request()->validate([
            'locale' => 'required|in:en,es',
        ]);

        app()->setLocale(request('locale'));

        if (auth()->check()) {
            auth()->user()->update(['locale' => request('locale')]);
        }

        return $this->dataResponse(trans_choice('actions.updated', 1));
    }
}
