<?php

namespace Tests\Feature\Services;

use App\Enums\ActivityTypes;
use App\Enums\DesignTypes;
use App\Jobs\CreateWatermark;
use App\Jobs\HandleActivity;
use App\Services\DesignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DesignServiceTest extends TestCase
{
    use RefreshDatabase;

    private DesignService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        Event::fake();
        $this->service = resolve(DesignService::class);
    }

    #[Test]
    public function it_can_create_a_design()
    {
        $design = $this->service->createDesign([
            'design_type' => DesignTypes::LOGO,
            'name' => 'Some Name',
        ], UploadedFile::fake()->image('some.webp'));

        $this->assertEquals('Some Name', $design->name);
        $this->assertEquals(DesignTypes::LOGO, $design->design_type);
        $this->assertEquals('some', $design->image->name);
    }

    #[Test]
    public function when_creating_a_design_it_dispatches_handle_activity_job()
    {
        $this->service->createDesign([
            'design_type' => DesignTypes::LOGO,
            'name' => 'Some Name',
        ], UploadedFile::fake()->image('some.webp'));

        Queue::assertPushed(HandleActivity::class, function (HandleActivity $job) {
            return $job->activityType === ActivityTypes::NEW_DESIGN;
        });
    }

    #[Test]
    public function when_creating_a_design_user_can_specify_sell_option()
    {
        $design = $this->service->createDesign([
            'design_type' => DesignTypes::LOGO,
            'name' => 'Some Name',
            'sell' => true,
            'price' => 123,
        ], UploadedFile::fake()->image('some.webp'), UploadedFile::fake()->image('untagged.webp'));

        $this->assertEquals('some', $design->image->name);

        $this->assertEquals('Some Name', $design->product->name);
        $this->assertEquals(123, $design->product->price);
        $this->assertEquals('untagged', $design->product->file->name);
    }

    #[Test]
    public function when_creating_a_design_user_can_specify_sell_option_with_exclusive()
    {
        $design = $this->service->createDesign([
            'design_type' => DesignTypes::LOGO,
            'name' => 'Some Name',
            'sell' => true,
            'exclusive' => true,
            'price' => 123,
        ], UploadedFile::fake()->image('some.webp'), UploadedFile::fake()->image('untagged.webp'));

        $this->assertTrue($design->product->exclusive);
    }

    #[Test]
    public function when_creating_a_design_with_sell_option_and_can_auto_tag_it_will_dispatch_create_watermark_job()
    {
        Queue::fake();

        $this->service = resolve(DesignService::class);

        $design = $this->service->createDesign([
            'design_type' => DesignTypes::LOGO,
            'name' => 'Some Name',
            'sell' => true,
            'auto_tag' => true,
            'price' => 123,
        ], UploadedFile::fake()->image('file.webp'));

        Queue::assertPushed(CreateWatermark::class, function (CreateWatermark $job) {
            return ! is_null($job->delay);
        });
    }
}
