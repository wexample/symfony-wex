<?php

namespace Wexample\SymfonyWex\Api\Dto;

use Wexample\SymfonyApi\Api\Dto\AbstractEntityDto;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyWex\Entity\Selection;

class SelectionDto extends AbstractEntityDto
{
    public ?string $title;

    /** The rules as they were written, newlines and comments included. */
    public string $patterns;

    public string $path;

    public ?string $dateCreated;

    /**
     * @param Selection $entity
     */
    public static function fromEntity(AbstractEntity $entity): self
    {
        $dto = parent::fromEntity($entity);

        $dto->title = $entity->getTitle();
        $dto->patterns = $entity->getPatterns();
        $dto->path = $entity->getPath();
        $dto->dateCreated = $entity->getDateCreated()?->format(DATE_ATOM);

        return $dto;
    }
}
