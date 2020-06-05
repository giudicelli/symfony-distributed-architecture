<?php

namespace giudicelli\DistributedArchitectureBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use giudicelli\DistributedArchitectureBundle\Entity\MasterCommand;

/**
 * @method null|MasterCommand find($id, $lockMode = null, $lockVersion = null)
 * @method null|MasterCommand findOneBy(array $criteria, array $orderBy = null)
 * @method MasterCommand[]    findAll()
 * @method MasterCommand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MasterCommandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MasterCommand::class);
    }

    public function findOnePending(): ?MasterCommand
    {
        return $this->createQueryBuilder('mc')
            ->andWhere('mc.status = :status')
            ->setParameter('status', MasterCommand::STATUS_PENDING)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function create(string $command, ?string $groupName, ?array $params): MasterCommand
    {
        /** @var null|MasterCommand */
        $masterCommand = $this->createQueryBuilder('mc')
            ->andWhere('mc.status = :status')
            ->andWhere('mc.command = :command')
            ->andWhere('mc.groupName = :groupName')
            ->setParameter('status', MasterCommand::STATUS_PENDING)
            ->setParameter('groupName', $groupName)
            ->setParameter('command', $command)
            ->getQuery()
            ->getOneOrNullResult()
        ;
        if ($masterCommand) {
            $masterCommand->setParams($params);
        } else {
            $masterCommand = new MasterCommand();
            $masterCommand
                ->setCommand($command)
                ->setGroupName($groupName)
                ->setParams($params)
                ->setStatus(MasterCommand::STATUS_PENDING)
                ->setCreatedAt(new \DateTime())
            ;
        }

        $this->getEntityManager()->persist($masterCommand);
        $this->getEntityManager()->flush();

        return $masterCommand;
    }

    public function update(MasterCommand $masterCommand): void
    {
        $this->getEntityManager()->persist($masterCommand);
        $this->getEntityManager()->flush();
    }
}
