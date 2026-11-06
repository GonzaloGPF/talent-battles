<?php

namespace Tests\Feature\Models;

use App\Models\Download;
use App\Traits\HasCreatedBy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DownloadTest extends TestCase
{
    use RefreshDatabase;

    private Download $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = make(Download::class);
    }

    #[Test]
    public function download_uses_created_by_trait()
    {
        $this->assertUsesTrait($this->model, HasCreatedBy::class);
    }

    #[Test]
    public function download_morphs_to()
    {
        $this->assertMorphsTo($this->model, 'downloable');
    }
}
