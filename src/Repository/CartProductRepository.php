<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Entity\CartProduct;
use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CartProduct|null find($id, $lockMode = null, $lockVersion = null)
 * @method CartProduct|null findOneBy(array $criteria, array $orderBy = null)
 * @method CartProduct[]    findAll()
 * @method CartProduct[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CartProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartProduct::class);
    }


    /**
     * @param Cart $cart
     * @param Product $product
     * @param int $quantity
     *
     * @return CartProduct
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createNew(Cart $cart, Product $product, int $quantity = 1): CartProduct
    {
        $cartProduct = (new CartProduct())
            ->setCart($cart)
            ->setQuantity($quantity)
            ->setProduct($product);

        $this->saveCartProduct($cartProduct);

        return $cartProduct;
    }

    public function getByCartAndProduct(Cart $cart, Product $product): ?CartProduct
    {
        return $this->findOneBy(['cart' => $cart, 'product' => $product]);
    }

    public function removeByCartAndProduct(Cart $cart, Product $product): void
    {
        $existingLink = $this->findOneBy(['cart' => $cart, 'product' => $product]);;
        if ($existingLink instanceof CartProduct) {
            $this->getEntityManager()->remove($existingLink);
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param CartProduct $entity
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function saveCartProduct(CartProduct $entity): void
    {
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->refresh($entity);
    }
}
