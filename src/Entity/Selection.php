<?php

namespace Wexample\SymfonyWex\Entity;

use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyApi\Attribute\ApiEntity;
use Wexample\SymfonyForms\Attribute\EntityForm;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Entity\Traits\HasTitleTrait;
use Wexample\SymfonyWex\Repository\SelectionRepository;

/**
 * A subset of the files of an app, said by the patterns that match it.
 *
 * A selection travels with the project it describes, so it is declared in a
 * record — `.wex/data/selection/<uuid>.json` — and this row is rebuilt from that
 * file and never written back to it. The declaration is named by its own
 * identity, so a selection retitled stays the same selection.
 *
 * What it selects is not decided here: the patterns are held as they were
 * written, and whoever walks a tree reads them.
 */
#[ApiEntity]
#[PseudocodeExport(inherited: true)]
#[EntityForm]
#[ORM\Entity(repositoryClass: SelectionRepository::class)]
#[ORM\Table(name: 'selection')]
class Selection extends AbstractEntity
{
    use HasTitleTrait;

    /** The file the selection is declared in, which is what says the app it belongs to. */
    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    protected string $path;

    /**
     * The rules, one per line, read in order and in the vocabulary a `.gitignore`
     * uses: a line matches, a line opening on `!` unmatches what the lines above
     * it had taken, `**` crosses directories and `#` opens a comment.
     *
     * That vocabulary is the whole reason there is no second way to combine
     * selections: two rules already compose, and the order of the lines already
     * says which wins.
     */
    #[ORM\Column(type: Types::TEXT)]
    protected string $patterns = '';

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

    public function getPatterns(): string
    {
        return $this->patterns;
    }

    public function setPatterns(string $patterns): self
    {
        $this->patterns = $patterns;

        return $this;
    }

    /**
     * The rules as a list, blank lines and comments left out — what a reader
     * walking a tree asks for, rather than the block a human typed.
     *
     * @return string[]
     */
    public function getPatternLines(): array
    {
        $lines = [];

        foreach (preg_split('/\R/', $this->patterns) as $line) {
            $line = trim($line);

            if ('' !== $line && ! str_starts_with($line, '#')) {
                $lines[] = $line;
            }
        }

        return $lines;
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
