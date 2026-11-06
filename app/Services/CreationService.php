<?php

namespace App\Services;

use App\Interfaces\HasFilesInterface;
use App\Interfaces\HasProductInterface;
use App\Jobs\CreateWatermark;
use App\Models\Design;
use App\Models\Track;
use Illuminate\Http\UploadedFile;

class CreationService
{
    public function __construct(protected FileService $fileService, protected ProductService $productService) {}

    /**
     * Method to process file and product when user decided sell
     *
     * @return HasFilesInterface|HasProductInterface|null
     */
    protected function processFileAndProduct(Design|Track $model, array $data, UploadedFile $file, ?UploadedFile $untaggedFile = null): Design|Track|null
    {
        if (empty($data['sell'])) {
            return null;
        }

        $productData = [
            'price' => $data['price'],
            'exclusive' => $data['exclusive'] ?? false,
        ];

        if (empty($data['auto_tag'])) {
            $this->fileService->saveFile($model, $file);
            $this->productService->createFrom($model, $productData, $untaggedFile);
        } else {
            $product = $this->productService->createFrom($model, $productData, $file);

            CreateWatermark::dispatch($model, $product->file)
                ->delay(now()->addSeconds(5));
        }

        return $model;
    }
}
