<?php

namespace Tests\Feature\Tracks;

use App\Models\File;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewTrackTest extends TestCase
{
    use RefreshDatabase;

    private Track $track;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        File::flushEventListeners();
        $this->user = create(User::class);
        $this->track = create(Track::class);
    }

    #[Test]
    public function it_let_view_all_tracks()
    {
        $data = $this->getJson(route('tracks.index'))
            ->assertSuccessful()
            ->json('data');

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_let_view_an_specific_track()
    {
        $data = $this->getJson(route('tracks.show', $this->track))
            ->assertSuccessful()
            ->json('data');

        $this->assertEquals($this->track->id, $data['id']);
    }
}
