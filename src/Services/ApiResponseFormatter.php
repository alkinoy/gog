<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Cart;
use App\Entity\Product;
use JetBrains\PhpStorm\ArrayShape;

class ApiResponseFormatter
{
    /**
     * @param array|Product[] $productList
     * @return array
     */
    public function formatProductList(array $productList): array
    {
        $result['productList'] = [];
        /** @var Product $item */
        foreach ($productList as $item) {
            $result['productList'][] = $this->formatProduct($item);
        }

        return $result;
    }

    #[ArrayShape(['id' => "int", 'title' => "string", 'price' => "float", 'currency' => "string"])]
    public function formatProduct(Product $product): array
    {
        return [
            'id' => $product->getId(),
            'title' => $product->getTitle(),
            'price' => $product->getPrice(),
            'currency' => $product->getCurrency()->getId()
        ];
    }

    #[ArrayShape(['id' => "int|null", 'productCount' => "int", 'itemsCount' => "int", 'totalAmount' => "array", 'productList' => "array"])]
    public function formatCartSummary(Cart $cart): array
    {
        $products = [];
        $items = 0;
        foreach ($cart->getCartProducts() as $cartProduct) {
            $products[] = [
                'product' => $this->formatProduct($cartProduct->getProduct()),
                'quantity' => $cartProduct->getQuantity()
            ];
            $items += $cartProduct->getQuantity();
        }

        return [
            'id' => $cart->getId(),
            'productCount' => $cart->getTotalProductInCart(),
            'itemsCount' => $items,
            'totalAmount' => $cart->getTotalProductAmount(),
            'productList' => $products
        ];
    }
}