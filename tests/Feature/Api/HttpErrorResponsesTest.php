<?php

namespace Tests\Feature\Api;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class HttpErrorResponsesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('api')->prefix('api')->group(function () {
            Route::get('testing/abort/{status}', fn (string $status) => abort((int) $status));
            Route::get('testing/maintenance', fn () => abort(503, 'Back in a minute', ['Retry-After' => 60]));
            Route::get('testing/expired-token', fn () => throw new TokenMismatchException);
            Route::get('testing/denied', fn () => throw new AuthorizationException('Not your battle'));
        });
    }

    #[Test]
    #[TestWith([403, 'unauthorized'])]
    #[TestWith([409, 'request_failed'])]
    #[TestWith([413, 'too_large'])]
    #[TestWith([503, 'server_error'])]
    public function http_errors_keep_their_status_code(int $status, string $message)
    {
        $this->getJson("api/testing/abort/$status")
            ->assertStatus($status)
            ->assertJsonPath('message', trans("exceptions.$message"));
    }

    #[Test]
    public function http_errors_keep_their_message_and_headers()
    {
        $this->getJson('api/testing/maintenance')
            ->assertServiceUnavailable()
            ->assertJsonPath('message', 'Back in a minute')
            ->assertHeader('Retry-After', 60);
    }

    #[Test]
    public function a_missing_model_says_which_one_is_missing()
    {
        $this->getJson(route('users.show', 999999))
            ->assertNotFound()
            ->assertJsonPath('message', trans('exceptions.not_found_data', ['data' => modelTitle('user')]));
    }

    #[Test]
    public function an_expired_csrf_token_asks_the_spa_to_reload()
    {
        $this->getJson('api/testing/expired-token')
            ->assertUnauthorized()
            ->assertJsonPath('data.action', 'reload')
            ->assertJsonPath('data.message', trans('exceptions.session_expired'));
    }

    #[Test]
    public function a_denied_action_keeps_its_message()
    {
        $this->getJson('api/testing/denied')
            ->assertForbidden()
            ->assertJsonPath('message', 'Not your battle');
    }

    #[Test]
    #[TestWith(['es', 'Método DELETE no permitido'])]
    #[TestWith(['en', 'Method DELETE not allowed'])]
    public function a_wrong_verb_is_not_allowed_in_the_language_asked_for(string $language, string $message)
    {
        $this->withHeader('Accept-Language', $language)
            ->deleteJson(route('company.index'))
            ->assertMethodNotAllowed()
            ->assertHeader('Allow')
            ->assertJsonPath('message', $message);
    }
}
