<?php

namespace Wexample\SymfonyWex\Service;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Wexample\SymfonyWex\Process\ProcessTypeInterface;

/**
 * The treatments the board knows, which is the ones its bundles declare.
 *
 * The list is what is installed today: a process naming a type nothing declares
 * any more is not an error here, it is a declaration whose code has left.
 */
final readonly class ProcessTypeRegistry
{
    /**
     * @param iterable<ProcessTypeInterface> $types
     */
    public function __construct(
        #[AutowireIterator(ProcessTypeInterface::TAG)]
        private iterable $types,
    ) {
    }

    /**
     * @return array<string, ProcessTypeInterface> by the name they answer to
     */
    public function all(): array
    {
        $types = [];

        foreach ($this->types as $type) {
            $types[$type->getName()] = $type;
        }

        return $types;
    }

    public function get(string $name): ?ProcessTypeInterface
    {
        return $this->all()[$name] ?? null;
    }

    /**
     * The same list as what a human picking one reads.
     *
     * @return array<string, string> label to name
     */
    public function choices(): array
    {
        $choices = [];

        foreach ($this->all() as $type) {
            $choices[$type->getLabel()] = $type->getName();
        }

        return $choices;
    }
}
