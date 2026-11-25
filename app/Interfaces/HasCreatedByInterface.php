<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface HasCreatedByInterface
{
    public function createdBy(): BelongsTo;
}
