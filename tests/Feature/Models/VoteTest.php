<?php

namespace Tests\Feature\Models;

use App\Models\Battle;
use App\Models\Vote;
use App\Traits\HasCreatedBy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VoteTest extends TestCase
{
    use RefreshDatabase;

    private Vote $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = make(Vote::class);
    }

    #[Test]
    public function vote_uses_has_created_by_trait()
    {
        $this->assertUsesTrait($this->model, HasCreatedBy::class);
    }

    #[Test]
    public function vote_morphs_to_a_votable()
    {
        $this->assertMorphsTo($this->model, 'votable');
    }

    #[Test]
    public function vote_belongs_to_a_battle()
    {
        $this->assertBelongsTo($this->model, Battle::class);
    }
}
