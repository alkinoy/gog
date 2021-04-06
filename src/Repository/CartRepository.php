<?php

namespace App\Repository;

use App\Entity\Cart;
use App\Exception\Services\ResourceNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\ORMException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method Cart|null find($id, $lockMode = null, $lockVersion = null)
 * @method Cart|null findOneBy(array $criteria, array $orderBy = null)
 * @method Cart[]    findAll()
 * @method Cart[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CartRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
    }

    public function createNew(UserInterface $user): Cart
    {
        $cart = (new Cart())->setUser($user);
        $this->saveCart($cart);

        return $cart;
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
        $cart = $this->findOneBy(['id' => $cartId, 'user' => $user]);
        if (!($cart instanceof Cart)) {
            throw new ResourceNotFoundException('Cart not found');
        }

        return $cart;
    }

    /**
     * @param Cart $cart
     *
     * @throws ORMException
     */
    public function refresh(Cart $cart): void
    {
        $this->getEntityManager()->refresh($cart);
    }

    private function saveCart(Cart $cart): void
    {
        $this->getEntityManager()->persist($cart);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->refresh($cart);
    }
}
