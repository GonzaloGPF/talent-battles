<?php

namespace Tests\Unit\Rules;

use App\Rules\NameRule;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NameRuleTest extends TestCase
{
    #[Test]
    public function it_validates_names_correctly()
    {
        $this->assertFalse($this->isValid('Name Jack 123$@**'));
        $this->assertFalse($this->isValid('expl$@**'));
        $this->assertFalse($this->isValid('1234 Test #@$!'));
        $this->assertFalse($this->isValid('$329rest'));
        $this->assertFalse($this->isValid('John Olsen #3'));
        $this->assertFalse($this->isValid('¡test'));
        $this->assertFalse($this->isValid('/test'));
        $this->assertFalse($this->isValid('\test'));
        $this->assertFalse($this->isValid('·test'));
        $this->assertFalse($this->isValid('ºtest'));
        $this->assertFalse($this->isValid('=test'));
        $this->assertFalse($this->isValid('`test'));
        $this->assertFalse($this->isValid('Yu'));

        $this->assertTrue($this->isValid('Some_test'));
        $this->assertTrue($this->isValid('Some-test'));
        $this->assertTrue($this->isValid('Some.test'));
        $this->assertTrue($this->isValid('Name Jack 123'));
        $this->assertTrue($this->isValid('Raúl Pérez'));
        $this->assertTrue($this->isValid('Company & cia'));
    }

    #[Test]
    public function it_can_ignore_specific_characters()
    {
        $this->assertTrue($this->isValid('\test', ['\\']));
    }

    private function isValid(string $name, array $except = []): bool
    {
        $validator = Validator::make(['name' => $name], [
            'name' => new NameRule($except),
        ]);

        return ! $validator->fails();
    }
}
