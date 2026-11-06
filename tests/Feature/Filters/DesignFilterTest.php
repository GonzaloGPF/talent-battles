<?php

namespace Tests\Feature\Filters;

use App\Enums\AspectRatios;
use App\Enums\BattleStates;
use App\Enums\DesignTypes;
use App\Enums\Thematics;
use App\Models\Album;
use App\Models\Battle;
use App\Models\Design;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DesignFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        create(Design::class, [], 3);
    }

    #[Test]
    public function it_can_filter_designs_by_name()
    {
        create(Design::class, ['name' => 'Test']);

        $designs = Design::filter(['name' => 'Test'])->get();

        $this->assertCount(1, $designs);
    }

    #[Test]
    public function it_can_filter_designs_by_design_types()
    {
        Design::query()->update(['design_type' => DesignTypes::LOGO]);

        create(Design::class, ['design_type' => DesignTypes::FREE]);

        $designs = Design::filter(['design_types' => [DesignTypes::FREE]])->get();

        $this->assertCount(1, $designs);
    }

    #[Test]
    public function it_can_filter_designs_by_created_by()
    {
        $user = create(User::class);

        create(Design::class, ['created_by' => $user->id]);

        $designs = Design::filter(['created_by' => [$user->id]])->get();

        $this->assertCount(1, $designs);
    }

    #[Test]
    public function it_can_filter_designs_by_thematic()
    {
        Design::query()->update(['thematic' => null]);

        create(Design::class, ['thematic' => Thematics::NATURE]);

        $designs = Design::filter(['thematics' => [Thematics::NATURE->value]])->get();

        $this->assertCount(1, $designs);
    }

    #[Test]
    public function it_can_filter_designs_by_aspect_ration()
    {
        Design::query()->update(['aspect_ratio' => AspectRatios::TV_1_1]);

        create(Design::class, ['aspect_ratio' => AspectRatios::MOBILE_2_3]);

        $designs = Design::filter(['aspect_ratios' => [AspectRatios::MOBILE_2_3->value]])->get();

        $this->assertCount(1, $designs);
    }

    #[Test]
    public function it_can_filter_designs_by_promoted()
    {
        /** @var Design $design1 */
        $design1 = create(Design::class);

        create(Product::class, [
            'promoted' => today(),
            'orderable_type' => Design::class,
            'orderable_id' => $design1->id,
        ]);

        /** @var Design $design2 */
        $design2 = create(Design::class);

        create(Product::class, [
            'promoted' => today()->addMonth(),
            'orderable_type' => Design::class,
            'orderable_id' => $design2->id,
        ]);

        /** @var Design $design3 */
        $design3 = create(Design::class);

        create(Product::class, [
            'promoted' => today()->subMonth(),
            'orderable_type' => Design::class,
            'orderable_id' => $design3->id,
        ]);

        $designs = Design::filter(['promoted' => true])->get();

        $this->assertCount(2, $designs);
    }

    #[Test]
    public function it_can_filter_designs_by_can_challenge()
    {
        Design::query()->update(['can_challenge' => false]);

        create(Design::class, ['can_challenge' => true]);

        $designs = Design::filter(['can_challenge' => true])->get();

        $this->assertCount(1, $designs);

        $designs = Design::filter(['can_challenge' => false])->get();

        $this->assertCount(3, $designs);
    }

    #[Test]
    public function it_can_filter_designs_without_open_battles()
    {
        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::OPEN]);
        /** @var Design $lookingForBattle */
        $lookingForBattle = create(Design::class);
        $lookingForBattle->battles()->save($battle);

        /** @var Battle $battle */
        $battle = create(Battle::class, ['battle_state' => BattleStates::IN_PROGRESS]);
        /** @var Design $alreadyInBattle */
        $alreadyInBattle = create(Design::class);
        $alreadyInBattle->battles()->save($battle);

        $designIds = Design::filter(['without_open_battles' => true])
            ->get()
            ->pluck('id')
            ->toArray();

        $this->assertCount(3, $designIds);
        $this->assertNotInArray($lookingForBattle->id, $designIds);
        $this->assertNotInArray($alreadyInBattle->id, $designIds);
    }

    #[Test]
    public function it_can_filter_designs_by_exclusive()
    {
        /** @var Design $design1 */
        $design1 = create(Design::class);
        create(Product::class, [
            'orderable_type' => Design::class,
            'orderable_id' => $design1->id,
            'exclusive' => true,
        ]);
        create(Product::class, [
            'orderable_type' => Design::class,
            'orderable_id' => $design1->id,
            'exclusive' => false,
        ]);
        /** @var Design $design2 */
        $design2 = create(Design::class);
        create(Product::class, [
            'orderable_type' => Design::class,
            'orderable_id' => $design2->id,
            'exclusive' => false,
        ]);

        $designs = Design::filter(['exclusive' => true])->get();

        $this->assertCount(1, $designs);

        $designs = Design::filter(['exclusive' => false])->get();

        $this->assertCount(2, $designs);
    }

    #[Test]
    public function it_can_filter_designs_by_in_sale()
    {
        /** @var Design $design */
        $design = create(Design::class);
        create(Product::class, [
            'orderable_type' => Design::class,
            'orderable_id' => $design->id,
        ]);

        $designs = Design::filter(['in_sale' => true])->get();

        $this->assertCount(1, $designs);

        $designs = Design::filter(['in_sale' => false])->get();

        $this->assertCount(3, $designs);
    }

    #[Test]
    public function it_can_filter_designs_by_sold()
    {
        /** @var Design $design1 */
        $design1 = create(Design::class);
        create(Product::class, [
            'orderable_type' => Design::class,
            'orderable_id' => $design1->id,
            'sales' => 0,
        ]);
        create(Product::class, [
            'orderable_type' => Design::class,
            'orderable_id' => $design1->id,
            'sales' => 1,
        ]);
        /** @var Design $design2 */
        $design2 = create(Design::class);
        create(Product::class, [
            'orderable_type' => Design::class,
            'orderable_id' => $design2->id,
            'sales' => 1,
        ]);

        $designs = Design::filter(['sold' => true])->get();

        $this->assertCount(2, $designs);

        $designs = Design::filter(['sold' => false])->get();

        $this->assertCount(1, $designs);
    }

    #[Test]
    public function it_can_filter_designs_by_tags()
    {
        Design::query()->update(['tags' => null]);

        create(Design::class, ['tags' => ['some', 'tags', 'test']]);

        $data = Design::filter(['tags' => ['test']])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_designs_by_album()
    {
        Design::query()->update(['album_id' => null]);

        /** @var Album $album */
        $album = create(Album::class);
        create(Design::class, ['album_id' => $album->id]);

        $data = Design::filter(['album' => [$album->id]])->get();

        $this->assertCount(1, $data);
    }
}
