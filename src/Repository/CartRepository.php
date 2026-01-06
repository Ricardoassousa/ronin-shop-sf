<?php

namespace App\Repository;

use App\Entity\Cart;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Cart>
 *
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

    /**
     * Finds all active carts that were created more than 30 days ago.
     *
     * @return Cart[]
     */
    public function findInactiveCartsOlderThan30Days()
    {
        $dateLimit = new DateTime();
        $dateLimit->modify('-30 days');

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('cart')
            ->from(Cart::class, 'cart')
            ->where('cart.status = :status')
            ->setParameter('status', Cart::STATUS_ACTIVE)
            ->andWhere('cart.createdAt < :dateLimit')
            ->setParameter('dateLimit', $dateLimit)
            ->getQuery();

        return $qb->getResult();
    }

}
