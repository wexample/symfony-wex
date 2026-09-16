<?php

namespace Wexample\SymfonyWex\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Yaml\Yaml;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyApi\Attribute\ApiEntity;
use Wexample\SymfonyForms\Attribute\EntityForm;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyLive\Attribute\LiveEntity;
use Wexample\SymfonyLive\Enum\LiveTopicAction;
use Wexample\SymfonyHelpers\Entity\Traits\HasTitleTrait;
use Wexample\SymfonyWex\Repository\ProcessRepository;

/**
 * A treatment declared on a selection of files: what to run, and on what.
 *
 * This is the declaration and not the running of it. What a process did, how
 * far it got and when, belongs to a run — another record, opened by whoever
 * starts one, and none of this row's business.
 *
 * Like a selection, it travels with the project it describes: it is declared in
 * `.wex/data/process/<uuid>.yml`, and this row is rebuilt from that file and
 * never written back to it.
 */
#[ApiEntity]
#[PseudocodeExport(inherited: true)]
#[EntityForm]
#[ORM\Entity(repositoryClass: ProcessRepository::class)]
#[ORM\Table(name: 'process')]
// Subscribable because its runs publish on it: a table of them listens to the
// process and hears about a run that did not exist when the page was drawn.
#[LiveEntity(actions: [LiveTopicAction::CREATE, LiveTopicAction::UPDATE])]
class Process extends AbstractEntity
{
    use HasTitleTrait;

    /** The file the process is declared in, which is what says the app it belongs to. */
    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    protected string $path;

    /**
     * What runs, named and not chosen from a list.
     *
     * Nothing here knows the types: a bundle mounted in the board brings the
     * code doing the work and the name it answers to, so a column holding an
     * enum would hold the list of the bundles installed the day it was written.
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    protected string $type = '';

    /**
     * The files it runs on.
     *
     * Null when the record names a selection nothing declares any more — the
     * process survives it, and says so, rather than disappearing with it.
     */
    #[ORM\ManyToOne(targetEntity: Selection::class)]
    // Set to null by the database rather than forbidding the delete: a selection
    // is deleted by taking its record away, and a row refusing to go would leave
    // a file the board no longer has.
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    protected ?Selection $selection = null;

    /**
     * What the type is told, as it was written.
     *
     * Held as a block and not as columns for the same reason the type is a name:
     * what an option means is the type's business, and a shape agreed here would
     * be a shape every type has to fit.
     */
    #[ORM\Column(type: Types::TEXT)]
    protected string $options = '';

    /** When the declaration was written, which is what tells two apart in a list. */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    protected ?DateTimeInterface $dateCreated = null;

    public function __construct(string $path)
    {
        parent::__construct();

        $this->path = $path;

        $this->setId(self::idFor($path));
    }

    /** The identity the declaration is named by, read straight off the file name. */
    public static function idFor(string $path): Uuid
    {
        return Uuid::fromString(pathinfo($path, PATHINFO_FILENAME));
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getSelection(): ?Selection
    {
        return $this->selection;
    }

    public function setSelection(?Selection $selection): self
    {
        $this->selection = $selection;

        return $this;
    }

    public function getOptions(): string
    {
        return $this->options;
    }

    public function setOptions(string $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * The options as the values they stand for, for the type about to read them.
     *
     * @return array<string, mixed>
     */
    public function getOptionValues(): array
    {
        return Yaml::parse($this->options) ?? [];
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
}
