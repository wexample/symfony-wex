<?php

namespace Wexample\SymfonyWex\Api\Dto;

use Wexample\SymfonyApi\Api\Dto\AbstractEntityDto;
use Wexample\SymfonyHelpers\Entity\AbstractEntity;
use Wexample\SymfonyWex\Entity\ProcessRun;

class ProcessRunDto extends AbstractEntityDto
{
    /** What was run, by identity: a caller showing it has the process already. */
    public ?string $process;

    public string $state;

    /** Null until the worker has counted what it has to get through. */
    public ?int $itemsTotal;

    public int $itemsDone;

    /** What the run produced, as the type chose to write it. */
    public string $data;

    public string $path;

    public ?string $dateCreated;

    public ?string $dateStarted;

    public ?string $dateEnded;

    /**
     * @param ProcessRun $entity
     */
    public static function fromEntity(AbstractEntity $entity): self
    {
        $dto = parent::fromEntity($entity);

        $dto->process = $entity->getProcess()?->getId()->toRfc4122();
        $dto->state = $entity->getState();
        $dto->itemsTotal = $entity->getItemsTotal();
        $dto->itemsDone = $entity->getItemsDone();
        $dto->data = $entity->getData();
        $dto->path = $entity->getPath();
        $dto->dateCreated = $entity->getDateCreated()?->format(DATE_ATOM);
        $dto->dateStarted = $entity->getDateStarted()?->format(DATE_ATOM);
        $dto->dateEnded = $entity->getDateEnded()?->format(DATE_ATOM);

        return $dto;
    }
}
