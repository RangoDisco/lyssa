<?php

namespace App\Repository;

use App\Entity\CaretakingAccess;
use App\Entity\User;
use App\Enum\CaretakerAccessLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CaretakingAccess>
 */
class CaretakingAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CaretakingAccess::class);
    }

    public function isCaretakerOf(User $user, User $patient, ?CaretakerAccessLevel $accessLevel = null): bool
    {
        $qb = $this->createQueryBuilder('ca')
            ->select('ca')
            ->andWhere('ca.caretaker = :caretaker')
            ->andWhere('ca.patient = :patient')
            ->setParameter('caretaker', $user)
            ->setParameter('patient', $patient)
            ->setMaxResults(1);

        // Access level check should only be done versus EDIT. Doesn't make sense to check if the user is readonly (if CaretakingAccess exists = at least readonly).
        if ($accessLevel !== null) {
            $qb->andWhere('ca.level = :level')
                ->setParameter('level', $accessLevel);
        }

        return $qb->getQuery()->getOneOrNullResult() !== null;
    }

    //    /**
    //     * @return CaretakingAccess[] Returns an array of CaretakingAccess objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?CaretakingAccess
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
