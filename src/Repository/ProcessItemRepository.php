<?php

namespace Wexample\SymfonyWex\Repository;

use Doctrine\ORM\QueryBuilder;
use Wexample\SymfonyHelpers\Repository\AbstractRepository;
use Wexample\SymfonyWex\Entity\Process;
use Wexample\SymfonyWex\Entity\ProcessItem;
use Wexample\SymfonyWex\Entity\Traits\Manipulator\ProcessItemEntityManipulatorTrait;

/**
 * @method ProcessItem|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProcessItem|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProcessItem[]    findAll()
 * @method ProcessItem[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method ProcessItem      saveNewProcessItem(Process $process, string $path)
 */
class ProcessItemRepository extends AbstractRepository
{
    use ProcessItemEntityManipulatorTrait;

    public function createNewProcessItem(Process $process, string $path): ProcessItem
    {
        return (new ProcessItem())
            ->setProcess($process)
            ->setPath($path);
    }

    /**
     * The files of a process that are not fine, by path, keyed by it.
     *
     * @return array<string, ProcessItem>
     */
    public function findByProcessKeyedByPath(Process $process): array
    {
        $items = [];

        foreach ($this->findBy(['process' => $process]) as $item) {
            $items[$item->getPath()] = $item;
        }

        return $items;
    }

    /**
     * The same, left open for a listing, narrowed to some states when asked.
     *
     * @param string[] $states
     */
    public function queryByProcess(Process $process, array $states = []): QueryBuilder
    {
        $alias = $this->getEntityQueryAlias();
        $builder = $this->createQueryBuilder($alias)
            ->where($this->queryField('process').' = :process')
            ->setParameter('process', $process)
            ->orderBy($alias.'.path', self::SORT_ASC);

        if ([] !== $states) {
            $builder
                ->andWhere($this->queryField('state').' IN (:states)')
                ->setParameter('states', $states);
        }

        return $builder;
    }

    /**
     * How many files of each process of those are in each state.
     *
     * @param Process[] $processes
     *
     * @return array<string, array<string, int>> process id, then state, to a count
     */
    public function countByProcessAndState(array $processes): array
    {
        if ([] === $processes) {
            return [];
        }

        $alias = $this->getEntityQueryAlias();
        $rows = $this->createQueryBuilder($alias)
            ->select('IDENTITY('.$alias.'.process) AS process', $alias.'.state AS state', 'COUNT('.$alias.'.id) AS total')
            ->where($alias.'.process IN (:processes)')
            ->setParameter('processes', $processes)
            ->groupBy('process', 'state')
            ->getQuery()
            ->getArrayResult();

        $counts = [];

        foreach ($rows as $row) {
            $counts[(string) $row['process']][$row['state']] = (int) $row['total'];
        }

        return $counts;
    }
}
