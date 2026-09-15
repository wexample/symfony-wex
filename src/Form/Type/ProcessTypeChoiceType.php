<?php

namespace Wexample\SymfonyWex\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wexample\SymfonyForms\Form\Type\SelectInputType;
use Wexample\SymfonyWex\Service\ProcessTypeRegistry;

/**
 * A select whose options are the treatments declared in this board.
 *
 * The choices come from the registry and not from the caller, unlike the
 * selections: what a process may run is the same everywhere, only what it runs
 * on depends on the app.
 */
class ProcessTypeChoiceType extends AbstractType
{
    public function __construct(
        private readonly ProcessTypeRegistry $registry,
    ) {
    }

    public function getParent(): string
    {
        return SelectInputType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // A type carries its own label: a bundle declaring one has no way of
            // writing in the translations of the form that offers it.
            'auto_translate_choices' => false,
            'choices' => $this->registry->choices(),
        ]);
    }
}
