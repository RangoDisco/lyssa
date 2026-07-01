<?php

namespace App\Repository;

use App\Entity\CaretakingAccess;
use App\Entity\Dispense;
use App\Entity\User;
use App\Enum\CaretakerAccessLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Dispense>
 */
class DispenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dispense::class);
    }

    public function hasAccess(User $user, Dispense $dispense, ?CaretakerAccessLevel $accessLevel = null): bool
    {
        $qb = $this->createQueryBuilder('d')
            ->select('d')
            ->innerJoin('d.prescription', 'pr')
            ->innerJoin('pr.patient', 'pt')
            ->innerJoin(CaretakingAccess::class, 'ca', 'ON', 'ca.patient = pt')
            ->andWhere('d = :dispense')
            ->setParameter('user', $user)
            ->setParameter('dispense', $dispense)
            ->setMaxResults(1);

        if ($accessLevel !== null) {
            $qb->andWhere('pr.patient = :user OR (ca.level = :accessLevel AND ca.caretaker = :user)');
            $qb->setParameter('accessLevel', $accessLevel);
        } else {
            $qb->andWhere('pr.patient = :user OR ca.caretaker = :user');
        }

        return $qb->getQuery()->getOneOrNullResult() === null;
    }

//    /**
//     * @return Dispense[] Returns an array of Dispense objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Dispense
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
