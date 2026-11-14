<?php

namespace Tests\Feature\Api;

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
    public function a_wrong_verb_is_not_allowed()
    {
        $this->deleteJson(route('company.index'))
            ->assertMethodNotAllowed()
            ->assertHeader('Allow');
    }
}
