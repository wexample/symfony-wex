<?php

namespace Wexample\SymfonyWex\Service;

use Doctrine\ORM\EntityManagerInterface;
use Wexample\SymfonyWex\Entity\ProcessRun;

/**
 * Reads one run back from its record, for whoever just heard that it moved.
 *
 * This is the whole of the return path on the board's side: wex writes the file
 * and rings, and the row is rebuilt from what is on disk rather than from what
 * the message says — so a worker that wrote and then failed to ring leaves a
 * file that still holds the truth.
 */
final readonly class ProcessRunRecordProjector extends AbstractRecordProjector
{
    public function __construct(
        WorkdirDataReader $reader,
        private ProcessRunHydrator $hydrator,
        EntityManagerInterface $entityManager,
    ) {
        parent::__construct($reader, $entityManager);
    }

    public function projectOne(ProcessRun $run): void
    {
        $this->project($run, fn (array $values) => $this->hydrator->hydrate($run, $values));
    }
}
