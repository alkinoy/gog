<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Currency;
use App\Exception\Services\CreateNewProductException;
use App\Exception\Services\ResourceNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Currency|null find($id, $lockMode = null, $lockVersion = null)
 * @method Currency|null findOneBy(array $criteria, array $orderBy = null)
 * @method Currency[]    findAll()
 * @method Currency[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CurrencyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Currency::class);
    }

    /**
     * @param string $currencyCode
     *
     * @return Currency
     *
     * @throws ResourceNotFoundException
     */
    public function getCurrencyById(string $currencyCode): Currency
    {
        $currency = $this->find($currencyCode);
        if (!($currency instanceof Currency)) {
            throw new ResourceNotFoundException('Currency not found for code ' . $currencyCode);
        }

        return $currency;
    }
}
