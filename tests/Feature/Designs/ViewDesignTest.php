<?php

namespace Tests\Feature\Designs;

use App\Models\Design;
use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewDesignTest extends TestCase
{
    use RefreshDatabase;

    private Design $design;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        File::flushEventListeners();
        $this->user = create(User::class);
        $this->design = create(Design::class);
    }

    #[Test]
    public function it_let_view_all_designs()
    {
        $data = $this->getJson(route('designs.index'))
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_let_view_an_specific_design()
    {
        $data = $this->getJson(route('designs.show', $this->design))
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals($this->design->id, $data['id']);
    }
}
