<?php

namespace App\Interfaces;

interface IsConstantInterface
{
    public function getLabel(): string;

    /**
     * @return array<string, mixed>
     */
    public static function getConstants(): array;

    public function getConstantKey(): string;
}
