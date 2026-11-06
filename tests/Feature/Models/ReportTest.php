<?php

namespace Tests\Feature\Models;

use App\Models\Report;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = create(Report::class);
    }

    #[Test]
    public function report_morphs_to()
    {
        $this->assertMorphsTo($this->model, 'reportable');
    }
}
