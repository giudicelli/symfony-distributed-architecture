<?php

namespace giudicelli\DistributedArchitectureBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use giudicelli\DistributedArchitectureBundle\Entity\ProcessStatus;

/**
 * @method null|ProcessStatus find($id, $lockMode = null, $lockVersion = null)
 * @method null|ProcessStatus findOneBy(array $criteria, array $orderBy = null)
 * @method ProcessStatus[]    findAll()
 * @method ProcessStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProcessStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProcessStatus::class);
    }

    public function deleteAll(): void
    {
        $this->createQueryBuilder('ps')
            ->delete()
            ->getQuery()
            ->getResult()
        ;
    }

    public function update(ProcessStatus $processStatus): void
    {
        $this->getEntityManager()->persist($processStatus);
        $this->getEntityManager()->flush();
    }
}
