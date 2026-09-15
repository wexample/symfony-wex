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
final readonly class ProcessRunRecordProjector
{
    public function __construct(
        private WorkdirDataReader $reader,
        private ProcessRunHydrator $hydrator,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function projectOne(ProcessRun $run): void
    {
        $this->hydrator->hydrate(
            $run,
            $this->reader->read($run->getPath())
        );

        $this->entityManager->persist($run);
        $this->entityManager->flush();
    }
}
