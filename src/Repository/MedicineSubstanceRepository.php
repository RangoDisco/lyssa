<?php

namespace App\Repository;

use App\Entity\MedicineSubstance;
use App\Entity\Substance;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MedicineSubstance>
 */
class MedicineSubstanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MedicineSubstance::class);
    }

    public function getBySubstanceQuery(Substance $substance): QueryBuilder
    {
        return $this->createQueryBuilder('ms')
            ->where('ms.substance = :substance')
            ->setParameter('substance', $substance);
    }

    public function findOneBySubstanceAndCis(Substance $substance, int $cis): ?MedicineSubstance
    {
        return $this->createQueryBuilder('ms')
            ->innerJoin('ms.medicine', 'm')
            ->where('ms.substance = :substance')
            ->andWhere('m.cis = :cis')
            ->setParameter('substance', $substance)
            ->setParameter('cis', $cis)
            ->getQuery()->getOneOrNullResult();
    }

    //    /**
    //     * @return MedicineSubstance[] Returns an array of MedicineSubstance objects
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

    //    public function findOneBySomeField($value): ?MedicineSubstance
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
