<?php

namespace Wexample\SymfonyWex\Repository;

use Symfony\Component\Uid\Uuid;
use Wexample\SymfonyHelpers\Repository\AbstractRepository;
use Wexample\SymfonyWex\Entity\Selection;
use Wexample\SymfonyWex\Entity\Traits\Manipulator\SelectionEntityManipulatorTrait;

/**
 * @method Selection|null find($id, $lockMode = null, $lockVersion = null)
 * @method Selection|null findOneBy(array $criteria, array $orderBy = null)
 * @method Selection[]    findAll()
 * @method Selection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method Selection      saveNewSelection(string $path)
 */
class SelectionRepository extends AbstractRepository
{
    use SelectionEntityManipulatorTrait;

    /**
     * A selection read from the declaration sitting at that path.
     *
     * Nothing else is set: the file owns what it says, so whoever read it
     * hydrates the row from those values rather than from memory.
     */
    public function createNewSelection(string $path): Selection
    {
        return new Selection($path);
    }

    /**
     * The selections declared under a directory, by title.
     *
     * Alphabetical and not by date: this answers a listing and a picker alike,
     * and somebody looking for one they wrote knows its name, not its age.
     *
     * What that directory is stays the caller's business: a selection knows the
     * file it comes from, and whoever mounted that file knows what it means.
     *
     * @return Selection[]
     */
    public function findByPathPrefix(string $prefix): array
    {
        return $this->createQueryBuilder('selection')
            ->where('selection.path LIKE :prefix')
            ->setParameter('prefix', addcslashes($prefix, '%_\\').'/%')
            ->orderBy('selection.title', self::SORT_ASC)
            ->getQuery()
            ->getResult();
    }

    /**
     * One selection, on condition that it is declared under that directory.
     *
     * Asked for this way rather than by identity alone wherever the address
     * names both: a selection of another app answering there would be shown,
     * and edited, under a name that does not hold it.
     */
    public function findByPathPrefixAndId(
        string $prefix,
        Uuid $id,
    ): ?Selection {
        foreach ($this->findByPathPrefix($prefix) as $selection) {
            if ($selection->getId()->equals($id)) {
                return $selection;
            }
        }

        return null;
    }
}
