<?php

namespace Tests\Feature\Models;

use App\Models\Email;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EmailTest extends TestCase
{
    use RefreshDatabase;

    private Email $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = create(Email::class);
    }

    #[Test]
    public function email_belongs_to_a_user()
    {
        $this->assertBelongsTo($this->model, User::class, 'createdBy');
    }
}
