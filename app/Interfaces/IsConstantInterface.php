<?php

namespace App\Interfaces;

interface IsConstantInterface
{
    public function getLabel(): string;

    public static function getConstants(): array;

    public function getConstantKey(): string;
}
