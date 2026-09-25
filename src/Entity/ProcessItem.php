<?php

namespace Wexample\SymfonyWex\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyApi\Attribute\ApiEntity;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyWex\Repository\ProcessItemRepository;

/**
 * Where one file stands for one process, now.
 *
 * A run is a dated fact; this is the state the facts add up to. It is what the
 * page of a process lists and what the app's home sums up: a fix on one file
 * moves that file and leaves the others where the last check put them, where
 * reading the last run alone would forget them all.
 *
 * A file a check found fine has a row too, once checks name those files
 * (`passed`); before that, a check only counted them, and « fine » was the
 * absence of a row.
 *
 * Nothing writes it but the board, from the runs it projects: there is no
 * record behind it, since the runs it is read from are the record, and it can
 * be dropped and read again from them.
 */
#[ApiEntity]
#[PseudocodeExport(inherited: true)]
#[ORM\Entity(repositoryClass: ProcessItemRepository::class)]
#[ORM\Table(name: 'process_item')]
#[ORM\UniqueConstraint(columns: ['process_id', 'path'])]
class ProcessItem extends AbstractEntity
{
    /** The last check found nothing wrong with it. */
    public const string STATE_OK = 'ok';

    /** The last check found something wrong with it. */
    public const string STATE_FAULT = 'fault';

    /** A run has it to go through and has not got to it yet. */
    public const string STATE_PENDING = 'pending';

    /** The file a run is on. */
    public const string STATE_PROCESSING = 'processing';

    /** A run has been through it and has not ended yet: what it found comes at the end. */
    public const string STATE_DONE = 'done';

    /** A run stopped on it. */
    public const string STATE_ERROR = 'error';

    #[ORM\ManyToOne(targetEntity: Process::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    protected Process $process;

    /** The file, relative to the app. */
    #[ORM\Column(type: Types::STRING, length: 1024)]
    protected string $path;

    #[ORM\Column(type: Types::STRING, length: 16)]
    protected string $state = self::STATE_FAULT;

    /**
     * What the last check said of it, as the JSON text of its verdicts. Kept
     * while a run goes through the file, so a run that ends without checking
     * leaves the file where it was.
     */
    #[ORM\Column(type: Types::TEXT)]
    protected string $verdicts = '';

    /** Whether one of its verdicts names a repair a fix would make. */
    #[ORM\Column(type: Types::BOOLEAN)]
    protected bool $fixable = false;

    /** Why the run stopped, for a file in error. */
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    protected ?string $error = null;

    /** The run that last moved it: an older one no longer does. */
    #[ORM\ManyToOne(targetEntity: ProcessRun::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    protected ?ProcessRun $run = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $dateUpdated = null;

    public function getProcess(): Process
    {
        return $this->process;
    }

    public function setProcess(Process $process): self
    {
        $this->process = $process;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getVerdicts(): string
    {
        return $this->verdicts;
    }

    public function setVerdicts(string $verdicts): self
    {
        $this->verdicts = $verdicts;

        return $this;
    }

    public function isFixable(): bool
    {
        return $this->fixable;
    }

    public function setFixable(bool $fixable): self
    {
        $this->fixable = $fixable;

        return $this;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function setError(?string $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function getRun(): ?ProcessRun
    {
        return $this->run;
    }

    public function setRun(?ProcessRun $run): self
    {
        $this->run = $run;

        return $this;
    }

    public function getDateUpdated(): ?DateTimeInterface
    {
        return $this->dateUpdated;
    }

    public function setDateUpdated(?DateTimeInterface $dateUpdated): self
    {
        $this->dateUpdated = $dateUpdated;

        return $this;
    }
}
