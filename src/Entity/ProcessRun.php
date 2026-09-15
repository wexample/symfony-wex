<?php

namespace Wexample\SymfonyWex\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Yaml\Yaml;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyApi\Attribute\ApiEntity;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyWex\Repository\ProcessRunRepository;

/**
 * One execution of a process: when it was asked for, how far it got, what it found.
 *
 * It is a record on disk like everything else here, and for a reason that is not
 * symmetry: wex is what executes, and wex has no database. Writing the run in the
 * app's own `.wex/data/process_run/` is what lets a process run on a repository
 * that has no board at all, and what makes the demand survive a broker that falls
 * over. The row is a projection of that file, and the file is gitignored: a
 * process is a declaration worth committing, a run is a dated fact that is not.
 *
 * Both ends write it — the board opens it, wex fills it in as it works — which is
 * why the two of them are never interested in the same fields at the same time.
 */
#[ApiEntity]
#[PseudocodeExport(inherited: true)]
#[ORM\Entity(repositoryClass: ProcessRunRepository::class)]
#[ORM\Table(name: 'process_run')]
class ProcessRun extends AbstractEntity
{
    /** Asked for, and waiting for a worker to pick it up. */
    public const string STATE_PENDING = 'pending';

    /** Picked up, and working. */
    public const string STATE_RUNNING = 'running';

    /** Done, having reached the end of what it had to do. */
    public const string STATE_COMPLETE = 'complete';

    /** Stopped on something it could not get past, the reason being in the data. */
    public const string STATE_FAILED = 'failed';

    /** The file the run is written in, which is what says the app it happened in. */
    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    protected string $path;

    /**
     * What was run.
     *
     * Null when the declaration has since been deleted: what happened still
     * happened, and a run losing its process is not a reason to forget it.
     */
    #[ORM\ManyToOne(targetEntity: Process::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    protected ?Process $process = null;

    #[ORM\Column(type: Types::STRING, length: 32)]
    protected string $state = self::STATE_PENDING;

    /**
     * How many files the run has to get through, once it has counted them.
     *
     * Null until then, which is not zero: a progress bar has nothing to show
     * before the worker has opened the selection.
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    protected ?int $itemsTotal = null;

    #[ORM\Column(type: Types::INTEGER)]
    protected int $itemsDone = 0;

    /**
     * What the run produced, as the type chose to write it.
     *
     * A block and not columns, like the options it answers: what a result means
     * is the type's business, and a shape agreed here would be a shape every
     * type has to fit.
     */
    #[ORM\Column(type: Types::TEXT)]
    protected string $data = '';

    /** When it was asked for. */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $dateCreated = null;

    /** When a worker picked it up, which is not when it was asked for. */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $dateStarted = null;

    /** When it stopped, whether it finished or failed. */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $dateEnded = null;

    public function __construct(string $path)
    {
        parent::__construct();

        $this->path = $path;

        $this->setId(self::idFor($path));
    }

    /** The identity the run is named by, read straight off the file name. */
    public static function idFor(string $path): Uuid
    {
        return Uuid::fromString(pathinfo($path, PATHINFO_FILENAME));
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getProcess(): ?Process
    {
        return $this->process;
    }

    public function setProcess(?Process $process): self
    {
        $this->process = $process;

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

    public function getItemsTotal(): ?int
    {
        return $this->itemsTotal;
    }

    public function setItemsTotal(?int $itemsTotal): self
    {
        $this->itemsTotal = $itemsTotal;

        return $this;
    }

    public function getItemsDone(): int
    {
        return $this->itemsDone;
    }

    public function setItemsDone(int $itemsDone): self
    {
        $this->itemsDone = $itemsDone;

        return $this;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * What the run produced, as the values it stands for.
     *
     * @return array<string, mixed>
     */
    public function getDataValues(): array
    {
        return Yaml::parse($this->data) ?? [];
    }

    public function getDateCreated(): ?DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(?DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getDateStarted(): ?DateTimeInterface
    {
        return $this->dateStarted;
    }

    public function setDateStarted(?DateTimeInterface $dateStarted): self
    {
        $this->dateStarted = $dateStarted;

        return $this;
    }

    public function getDateEnded(): ?DateTimeInterface
    {
        return $this->dateEnded;
    }

    public function setDateEnded(?DateTimeInterface $dateEnded): self
    {
        $this->dateEnded = $dateEnded;

        return $this;
    }
}
