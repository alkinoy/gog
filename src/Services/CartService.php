<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Exception\GogRuntimeException;
use App\Exception\Services\LimitExceedException;
use App\Exception\Services\ResourceNotFoundException;
use App\Repository\CartProductRepository;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\User\UserInterface;

class CartService
{
    public const MAX_PRODUCTS_IN_CART = 3;
    public const MAX_ITEMS = 10;

    private CartRepository $cartRepository;
    private CartProductRepository $cartProductRepository;
    private ProductRepository $productRepository;

    /**
     * CartService constructor.
     * @param CartRepository $cartRepository
     * @param CartProductRepository $cartProductRepository
     * @param ProductRepository $productRepository
     */
    public function __construct(
        CartRepository $cartRepository,
        CartProductRepository $cartProductRepository,
        ProductRepository $productRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->cartProductRepository = $cartProductRepository;
        $this->productRepository = $productRepository;
    }

    public function createCart(UserInterface $user): Cart
    {
        return $this->cartRepository->createNew($user);
    }

    /**
     * @param int $cartId
     * @param UserInterface $user
     *
     * @return Cart
     *
     * @throws ResourceNotFoundException
     */
    public function getCartByIdAndUser(int $cartId, UserInterface $user): Cart
    {
        return $this->cartRepository->getCartByIdAndUser($cartId, $user);
    }

    /**
     * @param int $cartId
     * @param int $productId
     * @param int $quantity
     * @param UserInterface $user
     *
     * @return Cart
     *
     * @throws GogRuntimeException
     * @throws LimitExceedException
     * @throws ResourceNotFoundException
     */
    public function addProductIntoCart(int $cartId, int $productId, int $quantity, UserInterface $user): Cart
    {
        $cart = $this->getCartByIdAndUser($cartId, $user);
        if (self::MAX_PRODUCTS_IN_CART === $cart->getTotalProductInCart()) {
            throw new LimitExceedException(
                'Cannot add more products into cart. Limit is '
                . self::MAX_PRODUCTS_IN_CART
            );
        }

        $product = $this->productRepository->getProductById($productId);
        $existingLink = $this->cartProductRepository->getByCartAndProduct($cart, $product);
        try {
            if ($existingLink instanceof CartProduct) {
                if (self::MAX_ITEMS < ($existingLink->getQuantity() + $quantity)) {
                    throw new LimitExceedException(
                        'Cannot add so many items of the same product into cart. Limit is '
                        . self::MAX_ITEMS
                    );
                }

                $existingLink->setQuantity($existingLink->getQuantity() + $quantity);
                $this->cartProductRepository->saveCartProduct($existingLink);
            } else {
                $this->cartProductRepository->createNew($cart, $product, $quantity);
            }

            $this->cartRepository->refresh($cart);
        } catch (ORMException $e) {
            throw new GogRuntimeException('ORM exception: ' . $e->getMessage(), 0, $e);
        }

        return $cart;
    }

    /**
     * @param int $cartId
     * @param int $productId
     * @param UserInterface $user
     *
     * @throws ResourceNotFoundException
     */
    public function removeProductFromCart(int $cartId, int $productId, UserInterface $user): void
    {
        $cart = $this->getCartByIdAndUser($cartId, $user);
        $product = $this->productRepository->getProductById($productId);
        $this->cartProductRepository->removeByCartAndProduct($cart, $product);
    }
}