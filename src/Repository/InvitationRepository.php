<?php

namespace App\Repository;

use App\Entity\CaretakingAccess;
use App\Entity\Invitation;
use App\Entity\User;
use App\Enum\CaretakerAccessLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Invitation>
 */
class InvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invitation::class);
    }

    public function hasAccess(User $user, Invitation $invitation, ?CaretakerAccessLevel $accessLevel = null): bool
    {
        $qb = $this->createQueryBuilder('i')
            ->innerJoin('i.patient', 'p')
            ->innerJoin(CaretakingAccess::class, 'ca', 'ON', 'ca.patient = p')
            ->andWhere('i :invitation')
            ->setParameter('user', $user)
            ->setParameter('invitation', $invitation);

        if ($accessLevel !== null) {
            $qb->andWhere('p = :user OR (ca.level = :accessLevel AND ca.caretaker = :user)');
            $qb->setParameter('accessLevel', $accessLevel);
        } else {
            $qb->andWhere('p = :user OR ca.caretaker = :user');
        }

        return $qb->getQuery()->getOneOrNullResult() !== null;
    }

    public function createAcessibleQueryBuilder(User $user): QueryBuilder
    {
        return $this->createQueryBuilder('i')
            ->innerJoin('i.patient', 'p')
            ->leftJoin('p.careTakerAccesses', 'ca')
            ->leftJoin('ca.caretaker', 'c')
            ->where('p = :user OR c = :user')
            ->setParameter('user', $user)
            ->orderBy('i.createdAt', 'DESC');
    }

//    /**
//     * @return Invitation[] Returns an array of Invitation objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Invitation
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
