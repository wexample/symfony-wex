<?php

namespace Wexample\SymfonyWex\Api\Dto;

use Wexample\SymfonyApi\Api\Dto\AbstractEntityDto;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyWex\Entity\ProcessItem;

class ProcessItemDto extends AbstractEntityDto
{
    /** The process, by identity: a caller listing the files of one has it already. */
    public string $process;

    public string $path;

    public string $state;

    /** What the last check said of it, the JSON text of its verdicts. */
    public string $verdicts;

    public bool $fixable;

    public ?string $error;

    public ?string $run;

    public ?string $dateUpdated;

    /**
     * @param ProcessItem $entity
     */
    public static function fromEntity(AbstractEntity $entity): self
    {
        $dto = parent::fromEntity($entity);

        $dto->process = $entity->getProcess()->getId()->toRfc4122();
        $dto->path = $entity->getPath();
        $dto->state = $entity->getState();
        $dto->verdicts = $entity->getVerdicts();
        $dto->fixable = $entity->isFixable();
        $dto->error = $entity->getError();
        $dto->run = $entity->getRun()?->getId()->toRfc4122();
        $dto->dateUpdated = $entity->getDateUpdated()?->format(DATE_ATOM);

        return $dto;
    }
}
