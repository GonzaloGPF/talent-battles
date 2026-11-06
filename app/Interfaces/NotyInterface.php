<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Model;

interface NotyInterface
{
    public function getMessageKeys(): array;

    public function getLevel(): ?string;

    public function getIcon(): ?string;

    public function getTarget(): Model;
}
