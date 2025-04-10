<?php

namespace App\Domain\Entities;

class Order
{
    private $id;

    private $products;

    public function __construct(array $products)
    {
        $this->products = $products;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getProducts(): array
    {
        return $this->products;
    }
}
