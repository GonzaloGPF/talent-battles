<?php

namespace Tests\Feature\Views;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BladeTemplatesTest extends TestCase
{
    /**
     * Blade splits templates with the PHP tokenizer. With short_open_tag on, as in production, it
     * reads "<?" as PHP and leaves the rest of the template uncompiled. No request in the tests can
     * show it, because short_open_tag cannot be changed at runtime, so the sources are checked.
     */
    #[Test]
    public function templates_have_no_php_open_tags()
    {
        $templatesWithOpenTags = collect(File::allFiles(resource_path('views')))
            ->filter(fn ($file) => str_contains($file->getContents(), '<?'))
            ->map(fn ($file) => $file->getRelativePathname())
            ->values()
            ->all();

        $this->assertSame([], $templatesWithOpenTags);
    }
}
