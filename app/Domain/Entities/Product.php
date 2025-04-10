<?php

namespace App\Domain\Entities;

class Product
{
    private $id;

    private $name;

    private $ingredients;

    public function __construct(int $id, string $name, array $ingredients = [])
    {
        $this->id = $id;
        $this->name = $name;
        $this->ingredients = $ingredients;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getIngredients(): array
    {
        return $this->ingredients;
    }
}
