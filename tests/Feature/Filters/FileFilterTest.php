<?php

namespace Tests\Feature\Filters;

use App\Enums\FileTypes;
use App\Models\File;
use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FileFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        create(File::class, [], 3); // Extra data
    }

    #[Test]
    public function it_can_filter_files_by_mime()
    {
        create(File::class, ['mime' => 'image/jpeg']);

        $data = File::filter(['mime' => 'image'])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_fetch_files_by_model_id()
    {
        File::query()->update(['fileable_id' => 1]);

        create(File::class, ['fileable_id' => 123]);

        $data = File::filter(['model_id' => 123])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_fetch_files_by_model_type()
    {
        File::query()->update(['fileable_type' => User::class]);

        create(File::class, ['fileable_type' => Track::class]);

        $data = File::filter(['model_type' => 'track'])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_files_by_file_type()
    {
        File::query()->update(['file_type' => FileTypes::IMAGE]);
        create(File::class, ['file_type' => FileTypes::AUDIO]);

        $data = File::filter(['file_types' => [FileTypes::AUDIO->value]])->get();

        $this->assertCount(1, $data);
    }

    #[Test]
    public function it_can_filter_files_by_name()
    {
        create(File::class, ['name' => 'Test']);

        $data = File::filter(['name' => 'Test'])->get();

        $this->assertCount(1, $data);
    }
}
