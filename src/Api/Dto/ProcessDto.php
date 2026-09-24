<?php

namespace Wexample\SymfonyWex\Api\Dto;

use Wexample\SymfonyApi\Api\Dto\AbstractEntityDto;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyWex\Entity\Process;

class ProcessDto extends AbstractEntityDto
{
    public ?string $title;

    public string $type;

    /** The selection it runs on, by identity: a caller showing it has it already. */
    public ?string $selectionId;

    /** What the type is told, as it was written. */
    public string $options;

    /** What what it finds means: `info`, `success`, `warning` or `error`. */
    public string $severity;

    public string $path;

    public ?string $dateCreated;

    /**
     * @param Process $entity
     */
    public static function fromEntity(AbstractEntity $entity): self
    {
        $dto = parent::fromEntity($entity);

        $dto->title = $entity->getTitle();
        $dto->type = $entity->getType();
        $dto->selectionId = $entity->getSelection()?->getId()->toRfc4122();
        $dto->options = $entity->getOptions();
        $dto->severity = $entity->getSeverity();
        $dto->path = $entity->getPath();
        $dto->dateCreated = $entity->getDateCreated()?->format(DATE_ATOM);

        return $dto;
    }
}
