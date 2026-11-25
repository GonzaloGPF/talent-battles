<?php

namespace App\Interfaces;

use App\Models\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

interface HasProductInterface
{
    /**
     * @return MorphOne<Product, covariant Model>
     */
    public function product(): MorphOne;
}
