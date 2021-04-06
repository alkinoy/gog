<?php

declare(strict_types=1);

namespace App\Services;

class ProductDefinitionDto
{
    private string $title;
    private float $price;
    private string $currencyCode;

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function setCurrencyCode(string $currencyCode): self
    {
        $this->currencyCode = $currencyCode;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getCurrencyCode(): ?string
    {
        return $this->currencyCode;
    }
}