<?php

namespace App\Infrastructure\Http\Controllers;

use App\Domain\UseCases\Order\PlaceOrderUseCase;
use App\Infrastructure\Http\Requests\OrderRequest;
use App\Infrastructure\Http\Resources\OrderResource;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    private $placeOrderUseCase;

    public function __construct(PlaceOrderUseCase $placeOrderUseCase)
    {
        $this->placeOrderUseCase = $placeOrderUseCase;
    }

    public function store(OrderRequest $request): JsonResponse
    {
        try {
            $order = $this->placeOrderUseCase->execute($request->validated()['products']);
            return (new OrderResource($order))->response()->setStatusCode(201);
        } catch (\DomainException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => [
                    'stock' => [$e->getMessage()],
                ],
            ], 422);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
