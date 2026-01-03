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
    * @param array $searchParams
    * @return QueryBuilder
    */
    public function findProductByFilterQuery(array $searchParams, bool $isCatalog = false)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product');

        if (array_key_exists('name', $searchParams)) {
            $qb->andWhere('product.name LIKE :name');
            $qb->setParameter('name', '%' . $searchParams['name'] . '%');
        }

        if (array_key_exists('sku', $searchParams)) {
            $qb->andWhere('product.sku = :sku');
            $qb->setParameter('sku', $searchParams['sku']);
        }

        if (array_key_exists('shortDescription', $searchParams)) {
            $qb->andWhere('product.shortDescription LIKE :shortDescription');
            $qb->setParameter('shortDescription', '%' . $searchParams['shortDescription'] . '%');
        }

        if (array_key_exists('minPrice', $searchParams) and array_key_exists('maxPrice', $searchParams)) {
            $qb->andWhere('product.price BETWEEN :minPrice AND :maxPrice');
            $qb->setParameter('minPrice', $searchParams['minPrice']);
            $qb->setParameter('maxPrice', $searchParams['maxPrice']);
        }

        if (array_key_exists('minPrice', $searchParams)) {
            $qb->andWhere('product.price >= :minPrice');
            $qb->setParameter('minPrice', $searchParams['minPrice']);
        }

        if (array_key_exists('maxPrice', $searchParams)) {
            $qb->andWhere('product.price <= :maxPrice');
            $qb->setParameter('maxPrice', $searchParams['maxPrice']);
        }

        if (array_key_exists('stock', $searchParams)) {
            $qb->andWhere('product.stock = :stock');
            $qb->setParameter('stock', $searchParams['stock']);
        }

        if ($isCatalog) {
            $qb->andWhere('product.isActive = :catalog');
            $qb->setParameter('catalog', $isCatalog);
        }

        if (!empty($searchParams['categoryId'])) {
            $qb->andWhere('product.category = :category');
            $qb->setParameter('category', $searchParams['categoryId']);
        }

        if (array_key_exists('startDate', $searchParams) and array_key_exists('endDate', $searchParams)) {
            $qb->andWhere('product.createdAt BETWEEN :startDate AND :endDate');
            $qb->setParameter('startDate', $searchParams['startDate']->format('Y-m-d'));
            $qb->setParameter('endDate', $searchParams['endDate']->format('Y-m-d'));
        }

        if (array_key_exists('startDate', $searchParams)) {
            $qb->andWhere('product.createdAt >= :createdAt');
            $qb->setParameter('createdAt', $searchParams['startDate']);
        }

        if (array_key_exists('endDate', $searchParams)) {
            $qb->andWhere('product.createdAt <= :updatedAt');
            $qb->setParameter('updatedAt', $searchParams['endDate']);
        }

        $qb->orderBy('product.createdAt', 'desc');

        return $qb->getQuery();
    }

    /**
    * Finds a single Product entity by its slug.
    *
    * This method searches the database for a product with the given slug.
    * It returns the Product entity if found, or null if no matching product exists.
    *
    * @param string $slug The slug to search for
    *
    * @return Product|null The product entity if found, otherwise null
    */
    public function findOneBySlug(string $slug)
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('product')
            ->from(Product::class, 'product')
            ->where('product.slug = :slug')
            ->setParameter('slug', $slug);

        return $qb->getQuery()->getOneOrNullResult();
    }

}