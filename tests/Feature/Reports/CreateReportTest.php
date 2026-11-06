<?php

namespace Tests\Feature\Reports;

use App\Enums\ReportStates;
use App\Models\Album;
use App\Models\Comment;
use App\Models\Report;
use App\Models\Track;
use App\Models\User;
use App\Notifications\ReportReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->signIn();
    }

    #[Test]
    public function it_let_users_create_a_report()
    {
        $this->makeRequest([
            'message' => 'This is a report message',
        ])->assertSuccessful();

        $report = Report::first();

        $this->assertEquals(auth()->id(), $report->created_by);
        $this->assertEquals(ReportStates::OPEN, $report->report_state);
        $this->assertEquals('This is a report message', $report->message);
    }

    #[Test]
    public function it_let_users_create_a_report_for_a_track()
    {
        /** @var Track $track */
        $track = create(Track::class, [
            'created_by' => create(User::class)->id,
        ]);

        $this->makeRequest([
            'message' => 'This is a report message',
            'model_type' => 'track',
            'model_id' => $track->id,
        ])->assertSuccessful();

        $report = Report::first();

        $this->assertEquals($track->id, $report->reportable_id);
        $this->assertEquals(Track::class, $report->reportable_type);
    }

    #[Test]
    public function it_let_users_create_a_report_for_an_user()
    {
        /** @var User $user */
        $user = create(User::class);

        $this->makeRequest([
            'message' => 'This is a report message',
            'model_type' => 'user',
            'model_id' => $user->id,
        ])->assertSuccessful();

        $report = Report::first();

        $this->assertEquals($user->id, $report->reportable_id);
        $this->assertEquals(User::class, $report->reportable_type);
    }

    #[Test]
    public function it_let_users_create_a_report_for_an_album()
    {
        /** @var Album $album */
        $album = create(Album::class, [
            'created_by' => create(User::class)->id,
        ]);

        $this->makeRequest([
            'message' => 'This is a report message',
            'model_type' => 'album',
            'model_id' => $album->id,
        ])->assertSuccessful();

        $report = Report::first();

        $this->assertEquals($album->id, $report->reportable_id);
        $this->assertEquals(Album::class, $report->reportable_type);
    }

    #[Test]
    public function it_let_users_create_a_report_for_a_comment()
    {
        /** @var Comment $comment */
        $comment = create(Comment::class, [
            'created_by' => create(User::class)->id,
        ]);

        $this->makeRequest([
            'message' => 'This is a report message',
            'model_type' => 'comment',
            'model_id' => $comment->id,
        ])->assertSuccessful();

        $report = Report::first();

        $this->assertEquals($comment->id, $report->reportable_id);
        $this->assertEquals(Comment::class, $report->reportable_type);
    }

    #[Test]
    public function when_creating_a_report_users_can_upload_a_file()
    {
        $this->makeRequest([
            'message' => 'Creating a report with files',
            'files' => [
                UploadedFile::fake()->create('test_1.txt'),
                UploadedFile::fake()->create('test_2.txt'),
            ],
        ])->assertSuccessful();

        $this->assertCount(2, Report::first()->files);
    }

    #[Test]
    public function when_creating_a_report_for_a_track_it_will_send_a_notification_to_reported_user()
    {
        Notification::fake();

        /** @var User $reportedUser */
        $reportedUser = create(User::class);

        /** @var Track $track */
        $track = create(Track::class, [
            'created_by' => $reportedUser->id,
        ]);

        $data = $this->makeRequest([
            'message' => 'This is a report message',
            'model_type' => 'track',
            'model_id' => $track->id,
        ])->assertSuccessful()
            ->json('data');

        Notification::assertSentTo($reportedUser, function (ReportReceived $notification) use ($track) {
            return $notification->report->reportable->id === $track->id;
        });
    }

    #[Test]
    public function it_wont_let_create_a_report_of_a_track_that_belongs_to_authenticated_user()
    {
        /** @var Track $track */
        $track = create(Track::class, [
            'created_by' => auth()->id(),
        ]);

        $this->makeRequest([
            'message' => 'This is a report message',
            'model_type' => 'track',
            'model_id' => $track->id,
        ])->assertUnprocessable();
    }

    #[Test]
    public function it_wont_let_create_a_report_of_a_track_if_already_has_one_open_from_same_user()
    {
        /** @var Track $track */
        $track = create(Track::class, ['created_by' => create(User::class)->id]);

        Report::factory()->pending()->create([
            'reportable_id' => $track->id,
            'reportable_type' => Track::class,
            'created_by' => auth()->id(),
        ]);

        $this->makeRequest([
            'message' => 'This is a report message',
            'model_type' => 'track',
            'model_id' => $track->id,
        ])->assertUnprocessable();
    }

    #[Test]
    public function non_authenticated_users_cannot_create_reports()
    {
        $this->logout();

        $this->makeRequest()
            ->assertForbidden();
    }

    private function makeRequest(array $attributes = []): TestResponse
    {
        return $this->postJson(route('reports.store'), $attributes);
    }
}
