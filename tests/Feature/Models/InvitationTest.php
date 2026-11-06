<?php

namespace Tests\Feature\Models;

use App\Models\Invitation;
use App\Models\User;
use App\Traits\HasCreatedBy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InvitationTest extends TestCase
{
    use RefreshDatabase;

    private Invitation $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = make(Invitation::class);
    }

    #[Test]
    public function invitation_belongs_to_an_user()
    {
        $this->assertBelongsTo($this->model, User::class);
    }

    #[Test]
    public function invitation_has_created_by_trait()
    {
        $this->assertUsesTrait($this->model, HasCreatedBy::class);
    }
}
