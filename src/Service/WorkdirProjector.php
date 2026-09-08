<?php

namespace Wexample\SymfonyWex\Service;

use Doctrine\ORM\EntityManagerInterface;
use Wexample\SymfonyWex\Entity\App;
use Wexample\SymfonyWex\Repository\AppRepository;

/**
 * Keeps the rows in step with what the workdirs say, for whoever asks for it.
 *
 * Nothing in this package calls it: the entity, the repository and the reader
 * are each usable alone, and an application persisting an App of its own is not
 * made to project anything. Which workdirs exist is likewise not known here —
 * the caller lists them, because it is the caller that mounts them.
 *
 * Reading is idempotent: identity comes from the mount path, so the same
 * workdir read twice writes the same row, and an empty database is filled by
 * the same call that brings a full one up to date.
 */
final readonly class WorkdirProjector
{
    public function __construct(
        private AppRepository $repository,
        private WorkdirReader $reader,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Projects those workdirs and drops the rows no longer among them.
     *
     * @param string[] $paths where each app is mounted
     *
     * @return array{0: int, 1: int} how many apps were projected, and how many rows dropped
     */
    public function project(array $paths): array
    {
        $seen = [];

        foreach ($paths as $path) {
            $app = $this->read($path);

            // A directory listed a moment ago and gone by now is simply not
            // there: this run drops the row it left, like any other.
            if (! $app) {
                continue;
            }

            $seen[$app->getId()->toRfc4122()] = true;
        }

        $dropped = 0;

        foreach ($this->repository->findAll() as $app) {
            if (! isset($seen[$app->getId()->toRfc4122()])) {
                $this->entityManager->remove($app);
                ++$dropped;
            }
        }

        $this->entityManager->flush();

        return [count($seen), $dropped];
    }

    /**
     * Projects one workdir, leaving every other row where it is.
     *
     * What a full run would find for that app, without walking the others:
     * this is what follows a write, so that the row shown next is the one the
     * file now dictates rather than the one just submitted.
     */
    public function projectOne(string $path): ?App
    {
        $app = $this->read($path);

        $this->entityManager->flush();

        return $app;
    }

    private function read(string $path): ?App
    {
        // The row is fetched before the files are read, and read into: the disk
        // is the source, so an existing row is overwritten rather than
        // reconciled, and which fields that covers is the reader's business.
        $app = $this->reader->read(
            $path,
            $this->repository->find(App::idFor($path)),
        );

        if ($app) {
            $this->entityManager->persist($app);
        }

        return $app;
    }
}
