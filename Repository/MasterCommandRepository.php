<?php

namespace giudicelli\DistributedArchitectureBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use giudicelli\DistributedArchitectureBundle\Entity\MasterCommand;

/**
 * @author Frédéric Giudicelli
 *
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

    /**
     * Delete all commands.
     */
    public function deleteAll(): void
    {
        $this->createQueryBuilder('mc')
            ->delete()
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Return one pending command.
     *
     * @return null|MasterCommand A pending command or null there is none
     */
    public function findOnePending(): ?MasterCommand
    {
        return $this->createQueryBuilder('mc')
            ->andWhere('mc.status = :status')
            ->setParameter('status', MasterCommand::STATUS_PENDING)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Create a command.
     *
     * @param string      $command   The command
     * @param null|string $groupName The optional group name
     * @param null|array  $params    The optional params
     *
     * @return MasterCommand The created command
     */
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

    /**
     * Update a command.
     *
     * @param MasterCommand $masterCommand The command to update
     */
    public function update(MasterCommand $masterCommand): void
    {
        $this->getEntityManager()->persist($masterCommand);
        $this->getEntityManager()->flush();
    }
}
