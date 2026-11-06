<?php

namespace Tests\Feature\Models;

use App\Enums\FileTypes;
use App\Models\File;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FileTest extends TestCase
{
    use RefreshDatabase;

    private File $file;

    protected function setUp(): void
    {
        parent::setUp();
        $this->file = make(File::class);
    }

    #[Test]
    public function it_sanitizes_file_name_correctly()
    {
        $this->file->name = 'Some._ ugly name.txt';

        $this->assertEquals('Some__ugly_name', $this->file->name);
    }

    #[Test]
    public function it_can_morphs_to()
    {
        $this->assertTrue(method_exists($this->file, 'fileable'));
    }

    #[Test]
    public function it_can_scope_by_file_type()
    {
        $this->assertInstanceOf(Builder::class, File::byTypes([FileTypes::IMAGE]));
    }

    #[Test]
    public function it_can_scope_by_image_file()
    {
        $this->assertInstanceOf(Builder::class, File::areImage());
    }

    #[Test]
    public function it_knows_its_file_extension_from_path()
    {
        $this->file->path = 'path/to/file.txt';

        $this->assertEquals('txt', $this->file->getExtension());
    }

    #[Test]
    public function it_knows_its_base_64_content()
    {
        $this->file->path = generateTestFile('someFile');
        $this->file->mime = 'someMime';

        $this->assertStringContainsString('data:someMime;base64,', $this->file->toBase64());
    }
}
