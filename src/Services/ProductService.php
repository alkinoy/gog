<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Product;
use App\Exception\GogRuntimeException;
use App\Exception\Services\CreateNewProductException;
use App\Exception\Services\ResourceNotFoundException;
use App\Repository\CurrencyRepository;
use App\Repository\ProductRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\ORMException;
use Exception;

class ProductService
{
    private ProductRepository $productRepository;
    private CurrencyRepository $currencyRepository;

    public function __construct(ProductRepository $productRepository, CurrencyRepository $currencyRepository)
    {
        $this->productRepository = $productRepository;
        $this->currencyRepository = $currencyRepository;
    }


    public function getProductList(int $startsFrom = 0, int $limit = 3): array
    {
        return $this->productRepository->getProductListWithOffset($startsFrom, $limit);
    }

    /**
     * @param ProductDefinitionDto $dto
     * @param Product $product
     *
     * @return Product
     *
     * @throws CreateNewProductException
     * @throws GogRuntimeException
     */
    public function storeProduct(ProductDefinitionDto $dto, Product $product = null): Product
    {
        try {
            $currency = $this->currencyRepository->getCurrencyById($dto->getCurrencyCode());
            if (null === $product) {
                $product = $this->productRepository->createNewProduct($dto->getTitle(), $dto->getPrice(), $currency);
            } else {
                $this->productRepository->updateProduct($product, $dto->getTitle(), $dto->getPrice(), $currency);
            }

        } catch (ResourceNotFoundException) {
            throw new CreateNewProductException('Currency not found for code ' . $dto->getCurrencyCode());
        } catch (UniqueConstraintViolationException) {
            throw new CreateNewProductException('Product name is not unique: ' . $dto->getTitle());
        } catch (Exception $e) {
            throw new GogRuntimeException('Unexpected exception: ' . $e->getMessage(), 0, $e);
        }

        return $product;
    }

    /**
     * @param int $productId
     *
     * @throws ResourceNotFoundException
     * @throws GogRuntimeException
     */
    public function removeProduct(int $productId): void
    {
        $product = $this->getProduct($productId);
        try {
            $this->productRepository->removeProduct($product);
        } catch (ORMException $e) {
            throw new GogRuntimeException('Unexpected exception: ' . $e->getMessage());
        }
    }

    /**
     * @param int $productId
     * @param ProductDefinitionDto $dto
     *
     * @return Product
     *
     * @throws CreateNewProductException
     * @throws GogRuntimeException
     * @throws ResourceNotFoundException
     */
    public function updateProductById(int $productId, ProductDefinitionDto $dto): Product
    {
        $product = $this->productRepository->getProductById($productId);
        $this->storeProduct($dto, $product);

        return $product;
    }

    /**
     * @param int $productId
     *
     * @return Product
     *
     * @throws ResourceNotFoundException
     */
    public function getProduct(int $productId): Product
    {
        return $this->productRepository->getProductById($productId);
    }
}