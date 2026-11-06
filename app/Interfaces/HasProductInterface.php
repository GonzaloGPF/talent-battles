<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Relations\MorphOne;

interface HasProductInterface
{
    public function product(): MorphOne;
}
