<?php

namespace Wexample\SymfonyWex\Repository;

use Wexample\SymfonyHelpers\Repository\AbstractRepository;
use Wexample\SymfonyWex\Entity\App;
use Wexample\SymfonyWex\Entity\Traits\Manipulator\AppEntityManipulatorTrait;

/**
 * @method App|null find($id, $lockMode = null, $lockVersion = null)
 * @method App|null findOneBy(array $criteria, array $orderBy = null)
 * @method App[]    findAll()
 * @method App[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AppRepository extends AbstractRepository
{
    use AppEntityManipulatorTrait;
}
