<?php

namespace Tests\Feature\Users;

use App\Enums\BattleStates;
use App\Enums\BattleTypes;
use App\Enums\EmailTypes;
use App\Enums\Roles;
use App\Enums\TrackTypes;
use App\Jobs\SendEmail;
use App\Models\Album;
use App\Models\Battle;
use App\Models\Design;
use App\Models\File;
use App\Models\Like;
use App\Models\Track;
use App\Models\User;
use App\Services\ProfileClaimService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class ProfileClaimTest extends TestCase
{
    use RefreshDatabase;

    private const PASSWORD = 'Secret-123';

    private User $producer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->producer = create(User::class, ['role' => Roles::USER, 'nick' => 'Seed Beats']);
    }

    #[Test]
    public function admins_get_a_link_that_opens_the_claim_page()
    {
        $this->signIn();

        $url = $this->postJson(route('users.claim.link', $this->producer))
            ->assertOk()
            ->json('data.url');

        $this->logout();
        $this->get($url)
            ->assertOk()
            ->assertSee('Seed Beats');
    }

    #[Test]
    #[TestWith([Roles::USER, Roles::USER])]
    #[TestWith([Roles::ADMIN, Roles::ADMIN])]
    #[TestWith([Roles::ADMIN, Roles::MANAGER])]
    public function only_admins_get_links_and_only_for_user_profiles(Roles $role, Roles $profileRole)
    {
        $this->signIn(create(User::class, ['role' => $role]));
        $profile = create(User::class, ['role' => $profileRole]);

        $this->postJson(route('users.claim.link', $profile))
            ->assertForbidden();
    }

    #[Test]
    public function guests_do_not_get_links()
    {
        $this->postJson(route('users.claim.link', $this->producer))
            ->assertUnauthorized();
    }

    #[Test]
    public function an_expired_or_altered_link_does_not_open_the_page()
    {
        $url = resolve(ProfileClaimService::class)->createLink($this->producer)['url'];

        $otherUser = create(User::class);
        $this->get(str_replace("users/{$this->producer->id}/", "users/{$otherUser->id}/", $url))->assertForbidden();

        $this->travel(ProfileClaimService::LINK_DAYS + 1)->days();
        $this->get($url)->assertForbidden();
    }

    #[Test]
    public function the_producer_keeps_the_profile_with_their_email_and_password()
    {
        Queue::fake([SendEmail::class]);
        $pageUrl = resolve(ProfileClaimService::class)->createLink($this->producer)['url'];

        $this->postJson($this->actionLinks()['claim_url'], $this->claimData())
            ->assertSuccessful()
            ->assertJsonPath('message', trans('actions.profile_claimed'))
            ->assertJsonPath('data.email', 'owner@mail.com');

        $producer = $this->producer->fresh();
        $this->assertAuthenticatedAs($producer, 'web');
        $this->assertTrue($producer->getAuthPassword() !== $this->producer->getAuthPassword());
        $this->assertNull($producer->verified_at);
        Queue::assertPushed(SendEmail::class, fn (SendEmail $job) => $job->email->email_type === EmailTypes::VERIFY_EMAIL
            && $job->email->to === ['owner@mail.com']);

        $this->get($pageUrl)->assertForbidden();
    }

    #[Test]
    #[TestWith(['email', ['email' => 'not-an-email']])]
    #[TestWith(['email', ['email' => 'taken@mail.com']])]
    #[TestWith(['password', ['password_confirmation' => 'Other-123']])]
    #[TestWith(['terms_and_conditions', ['terms_and_conditions' => false]])]
    public function keeping_the_profile_needs_what_registering_needs(string $field, array $data)
    {
        create(User::class, ['email' => 'taken@mail.com']);

        $this->postJson($this->actionLinks()['claim_url'], array_merge($this->claimData(), $data))
            ->assertUnprocessable();

        $this->assertGuest('web');
    }

    #[Test]
    public function the_producer_deletes_the_profile_with_its_music_and_battles()
    {
        Storage::fake();
        $rival = create(Track::class, ['created_by' => create(User::class)->id, 'track_type' => TrackTypes::BEAT]);
        $track = create(Track::class, ['created_by' => $this->producer->id, 'track_type' => TrackTypes::BEAT]);
        $audio = create(File::class, ['fileable_type' => Track::class, 'fileable_id' => $track->id]);
        $design = create(Design::class, ['created_by' => $this->producer->id]);
        $album = create(Album::class, ['created_by' => $this->producer->id]);
        $like = create(Like::class, ['likeable_type' => Track::class, 'likeable_id' => $track->id]);
        $battle = create(Battle::class, ['battle_state' => BattleStates::IN_PROGRESS->value, 'battle_type' => BattleTypes::TRACK]);
        $battle->tracks()->attach([$track->id, $rival->id]);

        $this->deleteJson($this->actionLinks()['decline_url'])
            ->assertAccepted()
            ->assertJsonPath('message', trans('actions.profile_deleted'));

        foreach ([$track, $audio, $design, $album, $like, $battle] as $model) {
            $this->assertModelMissing($model);
        }
        $this->assertModelExists($rival);
        $this->assertSoftDeleted($this->producer);
        $this->assertDatabaseMissing('users', ['nick' => 'Seed Beats']);
        $this->assertDatabaseMissing('users', ['email' => $this->producer->email]);
        $this->assertGuest('web');
    }

    #[Test]
    #[TestWith(['postJson'])]
    #[TestWith(['deleteJson'])]
    public function a_link_from_before_the_profile_changed_does_nothing(string $method)
    {
        $links = $this->actionLinks();
        $this->producer->update(['password' => 'Changed-123']);

        $this->{$method}($method === 'postJson' ? $links['claim_url'] : $links['decline_url'], $this->claimData())
            ->assertForbidden();

        $this->assertNotSoftDeleted($this->producer);
        $this->assertNotSame('owner@mail.com', $this->producer->fresh()->email);
    }

    #[Test]
    public function an_unsigned_link_does_nothing()
    {
        $this->postJson(route('users.claim.store', ['user' => $this->producer->id, 'key' => 'guess']), $this->claimData())
            ->assertUnauthorized();

        $this->deleteJson(route('users.claim.destroy', ['user' => $this->producer->id, 'key' => 'guess']))
            ->assertUnauthorized();

        $this->assertNotSoftDeleted($this->producer);
    }

    /**
     * @return array{claim_url: string, decline_url: string}
     */
    private function actionLinks(): array
    {
        return resolve(ProfileClaimService::class)->createActionLinks($this->producer, now()->addDay());
    }

    /**
     * @return array<string, mixed>
     */
    private function claimData(): array
    {
        return [
            'email' => 'owner@mail.com',
            'password' => self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
            'terms_and_conditions' => true,
        ];
    }
}
