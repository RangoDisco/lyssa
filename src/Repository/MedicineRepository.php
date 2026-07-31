<?php

namespace App\Repository;

use App\Entity\CaretakingAccess;
use App\Entity\Medicine;
use App\Entity\Substance;
use App\Entity\User;
use App\Enum\CaretakerAccessLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Medicine>
 */
class MedicineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Medicine::class);
    }

    public function hasAccess(Medicine $medicine, User $user, ?CaretakerAccessLevel $accessLevel = null): bool
    {
        $qb = $this->createQueryBuilder('m')
            ->innerJoin(CaretakingAccess::class, 'ca', 'ON', 'ca.patient = m.owner')
            ->where('m = :medicine')
            ->setParameter('medicine', $medicine)
            ->setParameter(':user', $user)
            ->setMaxResults(1);

        if ($accessLevel !== null) {
            $qb->andWhere("m.owner = :user OR (ca.level = :accessLevel AND ca.caretaker = :user)")
                ->setParameter('accessLevel', $accessLevel);
        } else {
            $qb->andWhere("m.owner = :user OR ca.caretaker = :user");
        }

        return $qb->getQuery()->getOneOrNullResult() === null;
    }

    public function getBySubstanceQuery(Substance $substance): QueryBuilder
    {
        return $this->createQueryBuilder('m')
            ->innerJoin('m.medicineSubstances', 'ms')
            ->innerJoin('ms.substance', 's')
            ->where('s = :substance')
            ->setParameter('substance', $substance);
    }

    //    /**
    //     * @return Medicine[] Returns an array of Medicine objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Medicine
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
