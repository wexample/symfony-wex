<?php

namespace Wexample\SymfonyWex\Repository;

use Symfony\Component\Uid\Uuid;
use Wexample\SymfonyHelpers\Repository\AbstractRepository;
use Wexample\SymfonyWex\Entity\ProcessType;
use Wexample\SymfonyWex\Entity\Traits\Manipulator\ProcessTypeEntityManipulatorTrait;

/**
 * @method ProcessType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProcessType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProcessType[]    findAll()
 * @method ProcessType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProcessTypeRepository extends AbstractRepository
{
    use ProcessTypeEntityManipulatorTrait;

    /**
     * A type under the identity the list gave it.
     *
     * Nothing else is set: what the type is comes from whoever declared it, so
     * whoever read the list fills the row from those values.
     */
    public function createNewProcessType(Uuid $id): ProcessType
    {
        return new ProcessType($id);
    }

    /** @return ProcessType[] in the order a human reads them */
    public function findAllByName(): array
    {
        return $this->findBy([], ['name' => 'ASC']);
    }
}
