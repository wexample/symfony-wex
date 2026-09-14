<?php

namespace Wexample\SymfonyWex\Service;

use Doctrine\ORM\EntityManagerInterface;
use Wexample\SymfonyWex\Entity\Selection;

/**
 * Reads one selection back from its record, for whoever just wrote that record.
 *
 * What the page shows next is then what a full sweep would find, so a value the
 * file refuses never looks accepted. Sweeping every mounted app is another job
 * and another caller's: this one is given the selection to re-read.
 */
final readonly class SelectionRecordProjector
{
    public function __construct(
        private WorkdirDataReader $reader,
        private SelectionHydrator $hydrator,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function projectOne(Selection $selection): void
    {
        $this->hydrator->hydrate(
            $selection,
            $this->reader->read($selection->getPath())
        );

        $this->entityManager->persist($selection);
        $this->entityManager->flush();
    }
}
