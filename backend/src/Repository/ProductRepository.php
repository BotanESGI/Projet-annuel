<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findByBestRated($limit): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.reviews', 'r')
            ->leftJoin('p.defaultCategory', 'c')
            ->addSelect('c')
            ->select('p.id, p.price, p.name, p.description, p.image, AVG(r.rating) as avgRating, c.id as categoryId')
            ->groupBy('p.id, c.id')
            ->orderBy('avgRating', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByPriceASC($limit): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.defaultCategory', 'c')
            ->select('p.id, p.price, p.name, p.description, p.image, c.id as categoryId')
            ->orderBy('p.price', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findMostSoldProduct(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.id, p.name, p.description, p.image, p.price, SUM(oi.quantity) as sales_count, c.id as categoryId')
            ->join('App\Entity\OrderItem', 'oi', 'WITH', 'oi.product = p')
            ->leftJoin('p.defaultCategory', 'c')
            ->groupBy('p.id, c.id, p.name, p.description, p.image, p.price')
            ->orderBy('sales_count', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findLatestProducts($limit): array
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.defaultCategory', 'c')
            ->select('p.id, p.price, p.name, p.description, p.image, p.createdAt, c.id as categoryId')
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return $this->createQueryBuilder('p')
            ->leftJoin('p.defaultCategory', 'c')
            ->select('p.id, p.price, p.name, p.description, p.image, c.id as categoryId')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
}