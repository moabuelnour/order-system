<?php

namespace App\Domain\Entities;

class Ingredient
{
    private $id;

    private $name;

    private $stock;

    private $initialStock;

    private $alertSent = false;

    public function __construct(string $name, float $stock, float $initialStock)
    {
        $this->name = $name;
        $this->stock = $stock;
        $this->initialStock = $initialStock;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStock(): float
    {
        return $this->stock;
    }

    public function getInitialStock(): float
    {
        return $this->initialStock;
    }

    public function reduceStock(float $amount): void
    {
        if ($this->stock < $amount) {
            throw new \DomainException("Insufficient stock for {$this->name}");
        }
        $this->stock -= $amount;
    }

    public function isBelowThreshold(): bool
    {
        return $this->stock <= ($this->initialStock * 0.5);
    }

    public function hasAlertSent(): bool
    {
        return $this->alertSent;
    }

    public function markAlertSent(): void
    {
        $this->alertSent = true;
    }
}
