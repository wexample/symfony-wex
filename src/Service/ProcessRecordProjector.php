<?php

namespace Wexample\SymfonyWex\Service;

use Doctrine\ORM\EntityManagerInterface;
use Wexample\SymfonyWex\Entity\Process;

/**
 * Reads one process back from its record, for whoever just wrote that record.
 *
 * What the page shows next is then what a full sweep would find, so a value the
 * file refuses never looks accepted. Sweeping every mounted app is another job
 * and another caller's: this one is given the process to re-read.
 */
final readonly class ProcessRecordProjector
{
    public function __construct(
        private WorkdirDataReader $reader,
        private ProcessHydrator $hydrator,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function projectOne(Process $process): void
    {
        $this->hydrator->hydrate(
            $process,
            $this->reader->read($process->getPath())
        );

        $this->entityManager->persist($process);
        $this->entityManager->flush();
    }
}
