<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\ProductResource;
use App\Interfaces\HasProductInterface;
use App\Models\Product;
use App\Services\FileService;
use App\Services\ProductService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $productService)
    {
        parent::__construct();
        $this->middleware('auth:sanctum')->only([
            'store',
            'update',
            'destroy',
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): JsonResponse
    {
        $this->loadRelations($product);

        return $this->showResponse(new ProductResource($product));
    }

    /**
     * Display a listing of the Product.
     */
    public function index(): JsonResponse
    {
        $products = Product::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(ProductResource::collection($products));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request): JsonResponse
    {
        /** @var HasProductInterface|Model $model */
        $model = morphedModel($request->get('model_type'), $request->get('model_id'));

        $product = $this->productService->create($request->validated(), $model, $this->getFile());

        return $this->storeResponse(new ProductResource($product));
    }

    /**
     * Update the specified resource in storage.
     *
     * @throws AuthorizationException
     */
    public function update(ProductRequest $request, Product $product, FileService $fileService): JsonResponse
    {
        $this->authorize('update', $product);

        $this->productService->update($product, $request->validated(), $this->getFile());

        return $this->updateResponse(new ProductResource($product));
    }

    /**
     * Remove the specified resource from storage.
     *
     *
     * @throws AuthorizationException
     */
    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        $this->destroyModel($product);

        return $this->destroyResponse(new ProductResource($product));
    }
}
