<?php

namespace App\Repository;

use App\Entity\Product;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends ServiceEntityRepository<Product>
 *
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

    /**
    * Returns a QueryBuilder for filtering products
    *
    * @param array $search Associative array of filter values (name, description, minPrice, maxPrice, isActive)
    * @return QueryBuilder
    */
    public function findProductByFilterQuery(array $searchParams)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product');
        
        if (array_key_exists('name', $searchParams)) {
            $qb->andWhere('product.name = ?1');
            $qb->setParameter(1, $searchParams['name']);
        }

        if (array_key_exists('sku', $searchParams)) {
            $qb->andWhere('product.sku = ?2');
            $qb->setParameter(2, $searchParams['sku']);
        }

        if (array_key_exists('minPrice', $searchParams) and array_key_exists('maxPrice', $searchParams)) {
            $qb->andWhere('product.price BETWEEN :minPrice AND :maxPrice');
            $qb->setParameter('minPrice', $searchParams['minPrice']);
            $qb->setParameter('maxPrice', $searchParams['maxPrice']);
        }

        if (array_key_exists('minPrice', $searchParams)) {
            $qb->andWhere('product.price >= ?3');
            $qb->setParameter(3, $searchParams['minPrice']);
        }

        if (array_key_exists('maxPrice', $searchParams)) {
            $qb->andWhere('product.price <= ?4');
            $qb->setParameter(4, $searchParams['maxPrice']);
        }

        if (array_key_exists('stock', $searchParams)) {
            $qb->andWhere('product.stock = ?4');
            $qb->setParameter(4, $searchParams['stock']);
        }

        if (array_key_exists('isActive', $searchParams)) {
            $qb->andWhere('product.isActive = ?5');
            $qb->setParameter(5, $searchParams['isActive']);
        }

        if (array_key_exists('startDate', $searchParams) and array_key_exists('endDate', $searchParams)) {
            $qb->andWhere('product.createdAt BETWEEN :startDate AND :endDate');
            $qb->setParameter('startDate', $searchParams['startDate']->format('Y-m-d'));
            $qb->setParameter('endDate', $searchParams['endDate']->format('Y-m-d'));
        }

        if (array_key_exists('startDate', $searchParams)) {
            $qb->andWhere('product.createdAt >= ?6');
            $qb->setParameter(6, $searchParams['startDate']);
        }

        if (array_key_exists('endDate', $searchParams)) {
            $qb->andWhere('product.createdAt <= ?7');
            $qb->setParameter(7, $searchParams['endDate']);
        }

        $qb->orderBy('product.id', 'desc');

        return $qb->getQuery();
    }

}