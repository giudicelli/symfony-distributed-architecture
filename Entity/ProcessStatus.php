<?php

namespace giudicelli\DistributedArchitectureBundle\Entity;

use App\Repository\ProcessStatusRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProcessStatusRepository::class)
 * @ORM\Table(indexes={
 *      @ORM\Index(name="process_status_group_name_idx", columns={"group_name"}),
 *      @ORM\Index(name="process_status_group_id_idx", columns={"group_id"}),
 *      @ORM\Index(name="process_status_started_at_idx", columns={"started_at"}),
 *      @ORM\Index(name="process_status_stopped_at_idx", columns={"stopped_at"}),
 *      @ORM\Index(name="process_status_last_seen_at_idx", columns={"last_seen_at"}),
 *      @ORM\Index(name="process_status_status_idx", columns={"status"}),
 *      @ORM\Index(name="process_status_host_idx", columns={"host"}),
 *      @ORM\Index(name="process_status_command_idx", columns={"command"})
 * })
 */
class ProcessStatus
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $groupName;

    /**
     * @ORM\Column(type="integer")
     */
    private $groupId;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $stoppedAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastSeenAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $host;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $command;

    /**
     * @ORM\Column(type="text")
     */
    private $output;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getGroupName(): ?string
    {
        return $this->groupName;
    }

    public function setGroupName(string $groupName): self
    {
        $this->groupName = $groupName;

        return $this;
    }

    public function getGroupId(): ?int
    {
        return $this->groupId;
    }

    public function setGroupId(int $groupId): self
    {
        $this->groupId = $groupId;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeInterface
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeInterface $startedAt): self
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getStoppedAt(): ?\DateTimeInterface
    {
        return $this->stoppedAt;
    }

    public function setStoppedAt(?\DateTimeInterface $stoppedAt): self
    {
        $this->stoppedAt = $stoppedAt;

        return $this;
    }

    public function getLastSeenAt(): ?\DateTimeInterface
    {
        return $this->lastSeenAt;
    }

    public function setLastSeenAt(?\DateTimeInterface $lastSeenAt): self
    {
        $this->lastSeenAt = $lastSeenAt;

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

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;

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

    public function getOutput(): ?string
    {
        return $this->output;
    }

    public function setOutput(string $output): self
    {
        $this->output = $output;

        return $this;
    }
}
