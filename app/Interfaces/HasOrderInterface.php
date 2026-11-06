<?php

namespace App\Interfaces;

use App\Models\Order;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Collection;

/**
 * Interface HasOrderInterface
 *
 * @property Collection $orders
 * @property Order $order
 * @property int $id
 */
interface HasOrderInterface
{
    public function order(): MorphOne;

    public function orders(): MorphMany;
}
