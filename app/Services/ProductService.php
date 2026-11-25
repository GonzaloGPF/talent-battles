<?php

namespace App\Services;

use App\Enums\ProductTypes;
use App\Interfaces\HasProductInterface;
use App\Models\Design;
use App\Models\Product;
use App\Models\Track;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class ProductService
{
    public function create(array $data, HasProductInterface&Model $model, ?UploadedFile $uploadedFile = null): Product
    {
        $product = (new Product)->forceFill([
            'product_type' => $this->getProductType($model, $data),
            'name' => $data['name'] ?? $model->getAttribute('name'),
            'description' => $data['description'] ?? $model->getAttribute('description'),
            'price' => $data['price'],
            'exclusive' => $data['exclusive'] ?? false,
        ]);

        $product->orderable()
            ->associate($model)
            ->save();

        if ($uploadedFile) {
            resolve(FileService::class)->saveFile($product, $uploadedFile);
        }

        return $product;
    }

    public function createFrom(HasProductInterface&Model $model, array $data, UploadedFile $uploadedFile, ?UploadedFile $untaggedFile = null): Product
    {
        $uploadedFile = $untaggedFile ?? $uploadedFile;

        return $this->create([
            'product_type' => $this->getProductType($model),
            'name' => $model->getAttribute('name'),
            'description' => null,
            'price' => $data['price'],
            'exclusive' => $data['exclusive'] ?? false,
        ], $model, $uploadedFile);
    }

    public function update(Product $product, array $data, ?UploadedFile $uploadedFile = null): Product
    {
        $product->update($data);

        if ($uploadedFile) {
            $fileService = resolve(FileService::class);

            if ($product->file) {
                $fileService->updateFile($product->file, $uploadedFile);
            } else {
                $fileService->saveFile($product, $uploadedFile);
            }
        }

        return $product;
    }

    private function getProductType(HasProductInterface&Model $model, ?array $data = [])
    {
        if ($model instanceof Track) {
            return ProductTypes::TRACK;
        }

        if ($model instanceof Design) {
            return ProductTypes::DESIGN;
        }

        return $data['product_type'] ?? null;
    }
}
