<?php

namespace Database\Seeders;

use App\Enums\ProductTypes;
use App\Models\Design;
use App\Models\Product;
use App\Models\Track;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        (new Product)
            ->forceFill([
                'product_type' => ProductTypes::DONATION,
                'name' => 'donation',
                'description' => 'Donation',
                'price' => 1,
            ])->save();

        (new Product)
            ->forceFill([
                'product_type' => ProductTypes::PROMO,
                'name' => 'promo',
                'description' => 'Promo',
                'price' => config('company.services.promo.price'),
            ])->save();

        (new Product)
            ->forceFill([
                'product_type' => ProductTypes::POINTS,
                'name' => 'points',
                'description' => 'Buy Points',
                'price' => config('company.buy.points.price'),
            ])->save();

        Track::all()
            ->filter(fn () => rand(0, 10) > 4)
            ->each(fn (Track $track) => create(Product::class, [
                'product_type' => ProductTypes::TRACK,
                'created_by' => $track->created_by,
                'orderable_type' => Track::class,
                'orderable_id' => $track->id,
                'name' => $track->name,
                'description' => $track->description,
            ]));

        Design::all()
            ->filter(fn () => rand(0, 10) > 4)
            ->each(fn (Design $design) => create(Product::class, [
                'product_type' => ProductTypes::DESIGN,
                'created_by' => $design->created_by,
                'orderable_type' => Design::class,
                'orderable_id' => $design->id,
                'name' => $design->name,
                'description' => $design->name,
            ]));
    }
}
