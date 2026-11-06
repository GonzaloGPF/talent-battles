<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService, private readonly CartService $cartService)
    {
        parent::__construct();
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        /** @var Order $order */
        $order = Order::find($id);

        if (! $order && $paymentIntentId = request('payment_intent_id')) {
            /** @var Order $order */
            $order = Order::withoutGlobalScopes()->firstWhere('meta->payment_intent_id', $paymentIntentId);
        }

        if (! $order) {
            throw new NotFoundHttpException;
        }

        return $this->showResponse(new OrderResource($order));
    }

    /**
     * Display a listing of the Order.
     */
    public function index(): JsonResponse
    {
        $orders = Order::filter(request()->all())
            ->getOrPaginate();

        return $this->indexResponse(OrderResource::collection($orders));
    }

    public function store(OrderRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        $email = $user?->email ?? $request->get('email');

        $order = $this->orderService->createFromCart($this->cartService->getOrCreateCart(), $email, [
            'payment_intent_id' => $request->get('payment_intent_id'),
        ]);

        if (! $order) {
            return $this->emptyResponse();
        }

        return $this->storeResponse((new OrderResource($order))
            ->additional([
                'return_url' => URL::signedRoute('orders.success', compact('order')),
            ]));
    }
}
