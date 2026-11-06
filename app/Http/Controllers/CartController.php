<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\Cart;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    protected bool $onlyAction = true;

    public function __construct(private readonly CartService $cartService)
    {
        parent::__construct();
    }

    /**
     * Display the specified resource.
     */
    public function show(): JsonResponse
    {
        $cart = Cart::first();

        if (! $cart) {
            return $this->emptyResponse();
        }

        $this->loadRelations($cart);

        return $this->showResponse(new CartResource($cart));
    }

    //    /**
    //     * Store a newly created resource in storage.
    //     *
    //     * @return JsonResponse
    //     */
    //    public function store(): JsonResponse
    //    {
    //        $cart = $this->cartService->getOrCreateCart();
    //
    //        return $this->storeResponse(new CartResource($cart));
    //    }

    /**
     * Store a newly created resource in storage.
     */
    public function update(CartItemRequest $request): JsonResponse
    {
        $cart = $this->cartService->getOrCreateCart();

        if ($request->get('deleting')) {
            $cart = $this->cartService->removeItems($cart, $request->validated('product_ids'));
        } else {
            $cart = $this->cartService->addItems($cart, $request->validated('items_data'));
        }

        return $this->updateResponse(new CartResource($cart));
    }

    //    /**
    //     * Remove the specified resource from storage.
    //     *
    //     *
    //     * @return JsonResponse
    //     * @throws AuthorizationException
    //     */
    //    public function destroy(): JsonResponse
    //    {
    //        $cart = $this->cartService->getOrCreateCart();
    //
    //        $this->destroyModel($cart);
    //
    //        return $this->destroyResponse(new CartResource($cart));
    //    }
}
