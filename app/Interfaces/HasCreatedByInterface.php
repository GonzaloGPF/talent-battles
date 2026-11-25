<?php

namespace App\Interfaces;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface HasCreatedByInterface
{
    /**
     * @return BelongsTo<User, covariant Model>
     */
    public function createdBy(): BelongsTo;
}
