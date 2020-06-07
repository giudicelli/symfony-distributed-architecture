<?php

namespace giudicelli\DistributedArchitectureBundle\Entity;

use App\Repository\MasterCommandRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * This entity is used to send commands to the master.
 *
 * @author Frédéric Giudicelli
 *
 * @ORM\Entity(repositoryClass=MasterCommandRepository::class)
 * @ORM\Table(indexes={
 *      @ORM\Index(name="master_command_status_idx", columns={"status"}),
 *      @ORM\Index(name="master_command_command_idx", columns={"command"}),
 *      @ORM\Index(name="master_command_group_name_idx", columns={"group_name"})
 * })
 */
class MasterCommand
{
    const STATUS_PENDING = 'pending';
    const STATUS_INPROGRESS = 'inprogress';
    const STATUS_DONE = 'done';

    const COMMAND_START_GROUP = 'start_group';
    const COMMAND_STOP_GROUP = 'stop_group';
    const COMMAND_START_ALL = 'start_all';
    const COMMAND_STOP_ALL = 'stop_all';
    const COMMAND_STOP = 'stop';

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $handledAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $command;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $groupName;

    /**
     * @ORM\Column(type="json", nullable=true)
     */
    private $params;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getHandledAt(): ?\DateTimeInterface
    {
        return $this->handledAt;
    }

    public function setHandledAt(?\DateTimeInterface $handledAt): self
    {
        $this->handledAt = $handledAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getCommand(): ?string
    {
        return $this->command;
    }

    public function setCommand(string $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    public function setGroupName(?string $groupName): self
    {
        $this->groupName = $groupName;

        return $this;
    }

    public function getParams(): ?array
    {
        return $this->params;
    }

    public function setParams(?array $params): self
    {
        $this->params = $params;

        return $this;
    }
}
