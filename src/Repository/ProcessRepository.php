<?php

namespace Wexample\SymfonyWex\Repository;

use Symfony\Component\Uid\Uuid;
use Wexample\SymfonyHelpers\Repository\AbstractRepository;
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
     * The processes declared under a directory, by title.
     *
     * @return Process[]
     */
    public function findByPathPrefix(string $prefix): array
    {
        return $this->createQueryBuilder('process')
            ->where('process.path LIKE :prefix')
            ->setParameter('prefix', addcslashes($prefix, '%_\\').'/%')
            ->orderBy('process.title', self::SORT_ASC)
            ->getQuery()
            ->getResult();
    }

    /**
     * One process, on condition that it is declared under that directory.
     *
     * Asked for this way rather than by identity alone wherever the address
     * names both: a process of another app answering there would be shown, and
     * edited, under a name that does not hold it.
     */
    public function findByPathPrefixAndId(
        string $prefix,
        Uuid $id,
    ): ?Process {
        foreach ($this->findByPathPrefix($prefix) as $process) {
            if ($process->getId()->equals($id)) {
                return $process;
            }
        }

        return null;
    }
}
