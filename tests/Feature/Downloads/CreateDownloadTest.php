<?php

namespace Tests\Feature\Downloads;

use App\Enums\ActivityTypes;
use App\Models\Activity;
use App\Models\Download;
use App\Models\File;
use App\Models\Track;
use App\Models\User;
use App\Notifications\DownloadReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateDownloadTest extends TestCase
{
    use RefreshDatabase;

    private Track $model;

    private File $file;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->user = $this->signIn();
        $this->model = create(Track::class, [
            'created_by' => create(User::class)->id,
        ]);
        $this->file = create(File::class, [
            'path' => generateTestFile('SomeFile'),
            'fileable_id' => $this->model->id,
            'fileable_type' => Track::class,
        ]);
    }

    #[Test]
    public function it_let_create_a_download_for_a_model()
    {
        $this->makeRequest()
            ->assertSuccessful();

        $this->assertCount(1, $this->model->fresh()->downloads);
    }

    #[Test]
    public function when_creating_a_download_it_will_save_an_activity_too()
    {
        $this->makeRequest()
            ->assertSuccessful();

        /** @var Activity $activity */
        $activity = Activity::first();

        $this->assertEquals(ActivityTypes::DO_DOWNLOAD, $activity->activity_type);
        $this->assertEquals($this->user->id, $activity->created_by);
        $this->assertEquals($this->model->created_by, $activity->receiver_id);
        $this->assertEquals($this->model->id, $activity->target_id);
        $this->assertEquals(Track::class, $activity->target_type);
    }

    #[Test]
    public function when_creating_a_download_it_will_send_a_notification()
    {
        Notification::fake();

        $data = $this->makeRequest()
            ->assertSuccessful()
            ->json('data');

        $download = Download::find($data['id']);

        Notification::assertSentTo($this->model->createdBy, function (DownloadReceived $notification) use ($download) {
            return $notification->download->id === $download->id;
        });
    }

    #[Test]
    public function it_wont_increment_downloads_if_downloading_twice()
    {
        $this->model->downloads()->save(make(Download::class, [
            'created_by' => $this->user->id,
            'downloable_type' => Track::class,
            'downloable_id' => $this->model->id,
        ]));

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertCount(1, $this->model->fresh()->downloads);
    }

    #[Test]
    public function it_wont_increment_downloads_if_downloader_if_author()
    {
        $this->model->created_by = auth()->id();
        $this->model->save();

        $this->makeRequest()
            ->assertSuccessful();

        $this->assertCount(0, $this->model->fresh()->downloads);
    }

    private function makeRequest(): TestResponse
    {
        return $this->postJson(route('downloads.store'), [
            'model_type' => 'track',
            'model_id' => $this->model->id,
            'file_id' => $this->file->id,
        ]);
    }
}
