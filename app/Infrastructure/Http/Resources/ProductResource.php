<?php

namespace App\Infrastructure\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    private $quantity;

    public function __construct($resource, $quantity = null)
    {
        parent::__construct($resource);
        $this->quantity = $quantity;
    }

    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->getId(),
            'quantity' => $this->quantity,
            'name' => $this->getName(),
        ];
    }
}
