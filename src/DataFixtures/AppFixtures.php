<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Currency;
use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Uid\Uuid;

class AppFixtures extends Fixture
{
    private const DEFAULT_CURRENCY_ID = 'USD';
    private UserPasswordEncoderInterface $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadCurrencyList($manager);
        $this->loadProductList($manager);
        $this->loadUsers($manager);
    }

    public function loadUsers(ObjectManager $manager)
    {
        $user = (new User())
            ->setUuid('1be3856a-2708-4fdf-b9aa-3c76167c564a')
            ->setRoles(['ROLE_USER', 'ROLE_ADMIN']);

        $user->setPassword($this->passwordEncoder->encodePassword($user, '123'));

        $manager->persist($user);
        $manager->flush();

        $user = (new User())
            ->setUuid('1be3856a-2708-4fdf-b9aa-3c76167c564b')
            ->setRoles(['ROLE_USER']);

        $user->setPassword($this->passwordEncoder->encodePassword($user, '123'));

        $manager->persist($user);
        $manager->flush();
    }

    /**
     * @param ObjectManager $manager
     *
     * @throws Exception
     */
    private function loadProductList(ObjectManager $manager): void
    {
        //default currency
        $currency = $manager->find(Currency::class, self::DEFAULT_CURRENCY_ID);
        if (!($currency instanceof Currency)) {
            throw new Exception('Default currency fixture not found');
        }

        $productList = [
            [1, 'Fallout', 1.99],
            [2, 'Don\'t Strave', 2.99],
            [3, 'Baldur\'s Gate', 3.99],
            [4, 'Icewind Dale', 4.99],
            [5, 'Bloodborne', 5.99],
        ];

        foreach ($productList as $item) {
            $product = new Product($item[1], $item[2], $currency, $item[0]);
            $manager->persist($product);
        }

        $manager->flush();
    }

    private function loadCurrencyList(ObjectManager $manager): void
    {
        $currencyList = [
            ['USD', 'US Dollar'],
            ['EUR', 'Euro'],
            ['PLN', 'Polish zloty'],
        ];

        foreach ($currencyList as $item) {
            $currency = new Currency($item[0], $item[1]);
            $manager->persist($currency);
        }

        $manager->flush();
    }
}
