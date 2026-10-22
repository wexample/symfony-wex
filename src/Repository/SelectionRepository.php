<?php

namespace Wexample\SymfonyWex\Repository;

use Symfony\Component\Uid\Uuid;
use Wexample\SymfonyHelpers\Repository\AbstractRepository;
use Wexample\SymfonyWex\Entity\App;
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
     * The selections of an app, by title.
     *
     * @return Selection[]
     */
    public function findByApp(App $app): array
    {
        return $this->findBy(['app' => $app], ['title' => self::SORT_ASC]);
    }

    /**
     * One selection, on condition that it belongs to that app.
     *
     * Asked for this way rather than by identity alone wherever the address
     * names both: a selection of another app answering there would be shown, and
     * edited, under a name that does not hold it.
     */
    public function findByAppAndId(
        App $app,
        Uuid $id,
    ): ?Selection {
        return $this->findOneBy(['app' => $app, 'id' => $id]);
    }
}
