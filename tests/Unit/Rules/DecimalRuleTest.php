<?php

namespace Tests\Unit\Rules;

use App\Rules\DecimalRule;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DecimalRuleTest extends TestCase
{
    #[Test]
    public function it_validates_decimals_correctly()
    {
        $this->assertTrue($this->isValid(8045));
        $this->assertTrue($this->isValid('8045'));
        $this->assertTrue($this->isValid('8045.12'));
        $this->assertFalse($this->isValid('8045,12'));
        $this->assertFalse($this->isValid(9999999));
    }

    private function isValid($price): bool
    {
        $validator = Validator::make(
            ['decimal' => $price],
            ['decimal' => new DecimalRule]
        );

        return ! $validator->fails();
    }
}
