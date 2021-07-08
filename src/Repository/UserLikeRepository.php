<?php

namespace App\Repository;

use App\Entity\UserLike;
use App\Message\Command\UserLikeMessage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserLike|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserLike|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserLike[]    findAll()
 * @method UserLike[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserLikeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserLike::class);
    }

    /**
     * This repo is used to get user is liked
     *
     * @param UserLikeMessage $userLikeMessage
     * @return int|mixed|string
     */
    public function findUserLike(UserLikeMessage $userLikeMessage) {

        return $this->createQueryBuilder('ul')
            ->innerJoin('ul.movie', 'm')
            ->innerJoin('ul.user', 'u')
            ->where('u.id = :uId')
            ->andWhere('m.id = :mId')
            ->setParameter('uId', $userLikeMessage->getUserId())
            ->setParameter('mId', $userLikeMessage->getMovieId())
            ->getQuery()->getOneOrNullResult();
    }

    // /**
    //  * @return UserLike[] Returns an array of UserLike objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserLike
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
