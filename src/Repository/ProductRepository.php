<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Currency;
use App\Entity\Product;
use App\Exception\Services\ResourceNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\OptimisticLockException;

/**
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function getProductListWithOffset(int $offset, int $limit): array
    {
        return $this->findBy([], ['id' => 'ASC'], $limit, $offset);
    }

    /**
     * @param string $name
     * @param float $price
     * @param Currency $currency
     *
     * @return Product
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createNewProduct(string $name, float $price, Currency $currency): Product
    {

        $product = new Product($name, $price, $currency);
        $this->saveProduct($product);

        return $product;


    }

    /**
     * @param Product $product
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function removeProduct(Product $product): void
    {
        $this->getEntityManager()->remove($product);
        $this->getEntityManager()->flush();
    }

    /**
     * @param Product $product
     * @param string $name
     * @param float $price
     * @param Currency $currency
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function updateProduct(Product $product, string $name, float $price, Currency $currency): void
    {
        $product->setTitle($name)
            ->setCurrency($currency)
            ->setPrice($price);

        $this->saveProduct($product);
    }

    /**
     * @param int $productId
     *
     * @return Product
     *
     * @throws ResourceNotFoundException
     */
    public function getProductById(int $productId): Product
    {
        $product = $this->find($productId);
        if (!($product instanceof Product)) {
            throw new ResourceNotFoundException('Product not found');
        }

        return $product;
    }

    /**
     * @param Product $product
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveProduct(Product $product): void
    {
        $this->getEntityManager()->persist($product);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->refresh($product);
    }
}
