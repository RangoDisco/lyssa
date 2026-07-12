<?php

namespace App\Repository;

use App\Entity\CaretakingAccess;
use App\Entity\Medication;
use App\Entity\User;
use App\Entity\Variant;
use App\Enum\CaretakerAccessLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Variant>
 */
class VariantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Variant::class);
    }

    public function hasAccess(Variant $variant, User $user, ?CaretakerAccessLevel $accessLevel = null): bool
    {
        $qb = $this->createQueryBuilder('v')
            ->select('v')
            ->innerJoin('v.medication', 'm')
            ->innerJoin(CaretakingAccess::class, 'ca', 'ON', 'ca.patient = m.owner')
            ->where('v = :variant')
            ->setParameter('variant', $variant)
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

    public function getByMedicationQuery(Medication $medication): QueryBuilder
    {
        return $this->createQueryBuilder('v')
            ->select('v')
            ->where('v.medication = :medication')
            ->setParameter('medication', $medication);
    }

    //    /**
    //     * @return Variant[] Returns an array of Variant objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('v.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Variant
    //    {
    //        return $this->createQueryBuilder('v')
    //            ->andWhere('v.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
