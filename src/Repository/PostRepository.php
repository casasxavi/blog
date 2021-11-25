<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function dameTags($tags)
    {

        return $this->getEntityManager()
        ->createQuery(
            'SELECT p, t, c, u FROM App\Entity\Post p
            LEFT JOIN p.tags t
            INNER JOIN p.category c
            INNER JOIN p.user u
            WHERE t.name = :tags
            ORDER BY p.createdAt DESC'
            )->setParameter('tags', $tags)->getResult();

    }


    public function dameCategory($category)
    {

        return $this->getEntityManager()
        ->createQuery(
            'SELECT p, t, c, u FROM App\Entity\Post p
            LEFT JOIN p.tags t
            INNER JOIN p.category c
            INNER JOIN p.user u
            WHERE c.slug = :category    
            ORDER BY p.createdAt DESC'
            )->setParameter('category', $category)->getResult();

    }


    public function postSearch($title)
    {

        return $this->getEntityManager()
        ->createQuery(
            'SELECT p FROM App\Entity\Post p
            WHERE p.title LIKE :title'
            )->setParameter('title', '%'.$title.'%')->getResult();

    }


    // /**
    //  * @return Post[] Returns an array of Post objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Post
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
