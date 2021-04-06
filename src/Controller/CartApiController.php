<?php

declare(strict_types=1);

namespace App\Controller;

use App\Exception\Services\LimitExceedException;
use App\Exception\Services\ResourceNotFoundException;
use App\Services\ApiResponseFormatter;
use App\Services\CartService;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class CartApiController extends AbstractApiController
{
    private Security $security;
    private CartService $cartService;
    private ApiResponseFormatter $formatter;
    private LoggerInterface $logger;

    public function __construct(
        Security $security,
        CartService $cartService,
        ApiResponseFormatter $formatter,
        LoggerInterface $logger
    ) {
        $this->security = $security;
        $this->cartService = $cartService;
        $this->formatter = $formatter;
        $this->logger = $logger;
    }

    public function createCart(): Response
    {
        $user = $this->security->getUser();
        $cart = $this->cartService->createCart($user);

        return $this->respond($this->formatter->formatCartSummary($cart), Response::HTTP_CREATED);
    }


    public function addProductIntoCart(int $cartId, int $productId, int $quantity): Response
    {
        $user = $this->security->getUser();
        try {
            $cart = $this->cartService->addProductIntoCart($cartId, $productId, $quantity, $user);

            return $this->respond($this->formatter->formatCartSummary($cart), Response::HTTP_CREATED);
        } catch (ResourceNotFoundException $e) {
            return $this->respond(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (LimitExceedException $e) {
            return $this->respond(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (Exception $e) {
            $errorId = uniqid();
            $this->logger->error(
                'Unexpected exception in ' . __METHOD__ . ': ' . $e->getMessage(),
                [
                    'errorId' => $errorId,
                    'trace' => $e->getTrace(),
                    'cartId' => $cartId,
                    'userId' => $user->getUsername(),
                ]
            );

            return $this->respond(['error' => 'Unexpected error. Id: ' . $errorId], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function removeProductFromCart(int $cartId, int $productId): Response
    {
        $user = $this->security->getUser();
        try {
            $this->cartService->removeProductFromCart($cartId, $productId, $user);
            $cart = $this->cartService->getCartByIdAndUser($cartId, $user);

            return $this->respond($this->formatter->formatCartSummary($cart), Response::HTTP_OK);
        } catch (ResourceNotFoundException $e) {
            return $this->respond(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            $errorId = uniqid();
            $this->logger->error(
                'Unexpected exception in ' . __METHOD__ . ': ' . $e->getMessage(),
                [
                    'errorId' => $errorId,
                    'trace' => $e->getTrace(),
                    'cartId' => $cartId,
                    'userId' => $user->getUsername(),
                ]
            );

            return $this->respond(['error' => 'Unexpected error. Id: ' . $errorId], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function listProductsInCart(int $cartId): Response
    {
        $user = $this->security->getUser();
        try {
            $cart = $this->cartService->getCartByIdAndUser($cartId, $user);

            return $this->respond($this->formatter->formatCartSummary($cart), Response::HTTP_OK);
        } catch (ResourceNotFoundException) {
            return $this->respond(['error' => 'Cart not found'], Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            $errorId = uniqid();
            $this->logger->error(
                'Unexpected exception in ' . __METHOD__ . ': ' . $e->getMessage(),
                [
                    'errorId' => $errorId,
                    'trace' => $e->getTrace(),
                    'cartId' => $cartId,
                    'userId' => $user->getUsername()
                ]
            );

            return $this->respond(['error' => 'Unexpected error. Id: ' . $errorId], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}