<?php

namespace Tests\Feature\Models;

use App\Models\Activity;
use App\Models\User;
use App\Traits\HasCreatedBy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    private Activity $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = make(Activity::class);
    }

    #[Test]
    public function it_belongs_to_a_receiver()
    {
        $this->assertBelongsTo($this->model, User::class, 'receiver');
    }

    #[Test]
    public function track_has_created_by_trait()
    {
        $this->assertUsesTrait($this->model, HasCreatedBy::class);
    }
}
