<?php

namespace App\Repository;

use App\Entity\LikedPost;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<LikedPost>
 *
 * @method LikedPost|null find($id, $lockMode = null, $lockVersion = null)
 * @method LikedPost|null findOneBy(array $criteria, array $orderBy = null)
 * @method LikedPost[]    findAll()
 * @method LikedPost[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LikedPostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LikedPost::class);
    }

    public function save(LikedPost $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LikedPost $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return LikedPost[] Returns an array of LikedPost objects
//     */
    public function countLastWeekLike($user): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.user = :user')
            ->setParameter('user', $user)
            ->andWhere("l.createdAt >= :date")
            ->setParameter('date', new \DateTimeImmutable('1 week ago'))
            ->orderBy('l.id', 'ASC')
            ->getQuery()
            ->getScalarResult()
        ;
    }

    public function countTotalLike($user): array
    {
        return $this->createQueryBuilder('l')
            ->where('l.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getScalarResult()
            ;
    }

    public function countLikeSortByWeek(): array
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id), WEEK(l.createdAt) as w')
            ->where('l.createdAt >= :date')
            ->setParameter(':date', new \DateTime('1 month ago'))
            ->groupBy('w')
            ->getQuery()
            ->getResult()
            ;
    }

//    public function findOneBySomeField($value): ?LikedPost
//    {
//        return $this->createQueryBuilder('l')
//            ->andWhere('l.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
