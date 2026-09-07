<?php

namespace Wexample\SymfonyWex\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Wexample\SymfonyForms\Attribute\EntityForm;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Entity\Traits\HasDescriptionTrait;
use Wexample\SymfonyHelpers\Entity\Traits\HasNameTrait;
use Wexample\SymfonyWex\Repository\AppRepository;

/**
 * An app administered through its `.wex` directory, projected into a row.
 *
 * The files own what they say: this row is rebuilt from them and never written
 * back to them. Two consequences are wired below — the identity comes from the
 * path, so reading the same app twice writes the same row, and nothing here is
 * authoritative enough to survive the directory it was read from.
 */
#[ORM\Entity(repositoryClass: AppRepository::class)]
#[ORM\Table(name: 'app')]
#[EntityForm]
class App extends AbstractEntity
{
    use HasDescriptionTrait;
    use HasNameTrait;

    /**
     * Fixed namespace the path is hashed under, so that the same app read in
     * another request or another process resolves to the same row.
     */
    public const ID_NAMESPACE = '0f4c2b1e-6d3a-5c88-9a17-3b8e42d7f6c1';

    /** Where the app is mounted, which is what tells it apart from another. */
    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    protected string $path;

    /** What the app calls itself: a site, a library, a service. */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $type = null;

    /** The version the app declares, which is its own and not wex's. */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $version = null;

    /** The version of wex the app was last configured with. */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    protected ?string $wexVersion = null;

    /** @var string[] the domains the app answers on */
    #[ORM\Column(type: Types::JSON)]
    protected array $domains = [];

    public function __construct(string $path)
    {
        parent::__construct();

        $this->path = $path;
        $this->setId(self::idFor($path));
    }

    /**
     * The row an app mounted there has, whether or not it has been read yet.
     */
    public static function idFor(string $path): Uuid
    {
        return Uuid::v5(Uuid::fromString(self::ID_NAMESPACE), $path);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getWexVersion(): ?string
    {
        return $this->wexVersion;
    }

    public function setWexVersion(?string $wexVersion): self
    {
        $this->wexVersion = $wexVersion;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getDomains(): array
    {
        return $this->domains;
    }

    /**
     * @param string[] $domains
     */
    public function setDomains(array $domains): self
    {
        $this->domains = $domains;

        return $this;
    }
}
