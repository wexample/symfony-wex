<?php

namespace Wexample\SymfonyWex\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Wexample\Pseudocode\Attribute\PseudocodeExport;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyHelpers\Entity\Traits\HasNameTrait;
use Wexample\SymfonyWex\Repository\ProcessTypeRepository;

/**
 * One treatment a process may name, as wex declares it.
 *
 * Nothing here runs: the code doing the work lives in the addon that brings the
 * type, and this row exists so a process can be written against a name that will
 * be understood when the run reaches a worker. Offering a type wex does not
 * declare is the one thing this record prevents.
 *
 * Not exposed by the API: the only reader is the form offering the choice, and
 * a front that listed treatments would be offering what it cannot run either.
 *
 * The name is the identity — `filestate:report`, the prefix naming the family
 * answering for it — and the uuid is derived from it where the list is served,
 * so the same type is the same row on every board.
 */
#[PseudocodeExport(inherited: true)]
#[ORM\Entity(repositoryClass: ProcessTypeRepository::class)]
#[ORM\Table(name: 'process_type')]
#[ORM\UniqueConstraint(columns: ['name'])]
class ProcessType extends AbstractEntity
{
    use HasNameTrait;

    /**
     * What a human choosing it reads. It comes from the type itself, since
     * whoever declares a treatment is the only one able to say what it does.
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    protected string $label;

    public function __construct(Uuid $id)
    {
        parent::__construct();

        $this->setId($id);
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): self
    {
        $this->label = $label;

        return $this;
    }
}
