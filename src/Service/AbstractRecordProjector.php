<?php

namespace Wexample\SymfonyWex\Service;

use Doctrine\ORM\EntityManagerInterface;
use Wexample\SymfonyWex\Interface\WorkdirRecordInterface;

/**
 * Reads one row back from its record, for whoever just wrote that record or
 * heard that it moved.
 *
 * What the page shows next is then what a full sweep would find, so a value the
 * file refuses never looks accepted. Sweeping every mounted app is another job
 * and another caller's: this one is given the row to re-read. Each kind keeps a
 * projector of its own, named by it, since its hydrator is its own.
 */
abstract readonly class AbstractRecordProjector
{
    public function __construct(
        private WorkdirDataReader $reader,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param callable(array<string, mixed>): mixed $hydrate fills the row from the
     *                                                       record's values
     */
    protected function project(
        WorkdirRecordInterface $record,
        callable $hydrate,
    ): void {
        $hydrate($this->reader->read($record->getPath()));

        $this->entityManager->persist($record);
        $this->entityManager->flush();
    }
}
