<?php

namespace Wexample\SymfonyWex\Repository;

use Symfony\Component\Uid\Uuid;
use Wexample\SymfonyHelpers\Repository\AbstractRepository;
use Wexample\SymfonyWex\Entity\App;
use Wexample\SymfonyWex\Entity\Process;
use Wexample\SymfonyWex\Entity\Traits\Manipulator\ProcessEntityManipulatorTrait;

/**
 * @method Process|null find($id, $lockMode = null, $lockVersion = null)
 * @method Process|null findOneBy(array $criteria, array $orderBy = null)
 * @method Process[]    findAll()
 * @method Process[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Process      saveNewProcess(string $path)
 */
class ProcessRepository extends AbstractRepository
{
    use ProcessEntityManipulatorTrait;

    /**
     * A process read from the declaration sitting at that path.
     *
     * Nothing else is set: the file owns what it says, so whoever read it
     * hydrates the row from those values rather than from memory.
     */
    public function createNewProcess(string $path): Process
    {
        return new Process($path);
    }

    /**
     * The processs of an app, by title.
     *
     * @return Process[]
     */
    public function findByApp(App $app): array
    {
        return $this->findBy(['app' => $app], ['title' => self::SORT_ASC]);
    }

    /**
     * One process, on condition that it belongs to that app.
     *
     * Asked for this way rather than by identity alone wherever the address
     * names both: a process of another app answering there would be shown, and
     * edited, under a name that does not hold it.
     */
    public function findByAppAndId(
        App $app,
        Uuid $id,
    ): ?Process {
        return $this->findOneBy(['app' => $app, 'id' => $id]);
    }
}
