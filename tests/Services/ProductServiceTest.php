<?php

declare(strict_types=1);

namespace App\Tests\Services;

use App\Entity\Currency;
use App\Entity\Product;
use App\Exception\GogRuntimeException;
use App\Exception\Services\CreateNewProductException;
use App\Exception\Services\ResourceNotFoundException;
use App\Repository\CurrencyRepository;
use App\Repository\ProductRepository;
use App\Services\ProductDefinitionDto;
use App\Services\ProductService;
use Doctrine\DBAL\Driver\SQLSrv\Exception\Error;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Util\Exception;

class ProductServiceTest extends TestCase
{

    private ProductRepository|MockObject $productRepository;
    private CurrencyRepository|MockObject $currencyRepository;
    private ProductService $service;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepository::class);
        $this->currencyRepository = $this->createMock(CurrencyRepository::class);
        
        $this->service = new ProductService($this->productRepository, $this->currencyRepository);
    }


    public function testGetProductList()
    {
        $page = 2;
        $perpage = 3;
        $result = ['id'=> 1];
        
        $this->productRepository->expects(self::atLeastOnce())->method('getProductListWithOffset')->
            with($page, $perpage)->willReturn($result);
        
        self::assertEquals($result, $this->service->getProductList($page, $perpage));
    }

    public function testGetProduct()
    {
        $productId = 111;
        $product = $this->createMock(Product::class);

        $this->productRepository->expects(self::atLeastOnce())->method('getProductById')->
        with($productId)->willReturn($product);

        self::assertEquals($product, $this->service->getProduct($productId));
    }

    public function testCreateNewProductSuccess()
    {
        $productName = 'Product';
        $price = 123.33;
        $currencyCode = 'USD';


        //if we want to be sure exactly all fields will be read Mock should be used instead of real DTO or Entity
        $inputDto = (new ProductDefinitionDto())->setPrice($price)->setTitle($productName)
            ->setCurrencyCode($currencyCode);
        $currency = new Currency('USD', 'US dollar');
        $product = new Product($productName, $price, $currency);

        $this->currencyRepository->expects(self::atLeastOnce())->method('getCurrencyById')
            ->with($currencyCode)->willReturn($currency);

        $this->productRepository->expects(self::once())->method('createNewProduct')
            ->with($productName, $price, $currency)->willReturn($product);
        $this->productRepository->expects(self::never())->method('updateProduct');

        self::assertEquals($product, $this->service->storeProduct($inputDto));
    }

    public function testCreateNewProductButWrongCurrency()
    {
        $this->expectException(CreateNewProductException::class);

        $productName = 'Product';
        $price = 123.33;
        $currencyCode = 'non-exists';


        //if we want to be sure exactly all fields will be read Mock should be used instead of real DTO or Entity
        $inputDto = (new ProductDefinitionDto())->setPrice($price)->setTitle($productName)
            ->setCurrencyCode($currencyCode);

        $this->currencyRepository->expects(self::atLeastOnce())->method('getCurrencyById')
            ->with($currencyCode)->willThrowException(new ResourceNotFoundException());

        $this->productRepository->expects(self::never())->method('updateProduct');
        $this->productRepository->expects(self::never())->method('createNewProduct');

        $this->service->storeProduct($inputDto);
    }

    public function testCreateNewProductButNonUnique()
    {
        $this->expectException(CreateNewProductException::class);

        $productName = 'Product non unique';
        $price = 123.33;
        $currencyCode = 'USD';


        //if we want to be sure exactly all fields will be read Mock should be used instead of real DTO or Entity
        $inputDto = (new ProductDefinitionDto())->setPrice($price)->setTitle($productName)
            ->setCurrencyCode($currencyCode);
        $currency = new Currency('USD', 'US dollar');

        $this->currencyRepository->expects(self::atLeastOnce())->method('getCurrencyById')
            ->with($currencyCode)->willReturn($currency);

        $this->productRepository->expects(self::never())->method('updateProduct');
        $this->productRepository->expects(self::once())->method('createNewProduct')
            ->with($productName, $price, $currency)
            ->willThrowException(new UniqueConstraintViolationException('', new Error('')));

        $this->service->storeProduct($inputDto);
    }

    public function testCreateNewProductButCommonError()
    {
        $this->expectException(GogRuntimeException::class);

        $productName = 'Product non unique';
        $price = 123.33;
        $currencyCode = 'USD';


        $inputDto = (new ProductDefinitionDto())->setPrice($price)->setTitle($productName)
            ->setCurrencyCode($currencyCode);

        $this->currencyRepository->expects(self::atLeastOnce())->method('getCurrencyById')
            ->with($currencyCode)->willThrowException(new Exception());

        $this->productRepository->expects(self::never())->method('updateProduct');
        $this->productRepository->expects(self::never())->method('createNewProduct');

        $this->service->storeProduct($inputDto);
    }

    public function testUpdateProductSuccess()
    {
        $newProductName = 'New Product';
        $oldProductName = 'Old Product';
        $price = 123.33;
        $currencyCode = 'USD';


        //if we want to be sure exactly all fields will be read Mock should be used instead of real DTO or Entity
        $inputDto = (new ProductDefinitionDto())->setPrice($price)->setTitle($newProductName)
            ->setCurrencyCode($currencyCode);
        $currency = new Currency('USD', 'US dollar');
        $product = new Product($oldProductName, $price, $currency);

        $this->currencyRepository->expects(self::atLeastOnce())->method('getCurrencyById')
            ->with($currencyCode)->willReturn($currency);

        $this->productRepository->expects(self::once())->method('updateProduct')
            ->with($product, $newProductName, $price, $currency);
        $this->productRepository->expects(self::never())->method('createNewProduct');

        $this->service->storeProduct($inputDto, $product);
    }

}