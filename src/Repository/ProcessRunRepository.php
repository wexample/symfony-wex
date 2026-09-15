<?php

namespace Wexample\SymfonyWex\Repository;

use Wexample\SymfonyHelpers\Repository\AbstractRepository;
use Wexample\SymfonyWex\Entity\Process;
use Wexample\SymfonyWex\Entity\ProcessRun;
use Wexample\SymfonyWex\Entity\Traits\Manipulator\ProcessRunEntityManipulatorTrait;

/**
 * @method ProcessRun|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProcessRun|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProcessRun[]    findAll()
 * @method ProcessRun[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method ProcessRun      saveNewProcessRun(string $path)
 */
class ProcessRunRepository extends AbstractRepository
{
    use ProcessRunEntityManipulatorTrait;

    /**
     * A run read from the file sitting at that path.
     *
     * Nothing else is set: the file owns what it says, and both the board that
     * opened it and the worker that advanced it write there rather than here.
     */
    public function createNewProcessRun(string $path): ProcessRun
    {
        return new ProcessRun($path);
    }

    /**
     * What a process has been through, most recent first.
     *
     * @return ProcessRun[]
     */
    public function findByProcess(Process $process): array
    {
        return $this->findBy(
            ['process' => $process],
            ['dateCreated' => self::SORT_DESC]
        );
    }
}
