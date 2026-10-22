<?php

namespace Wexample\SymfonyWex\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wexample\SymfonyForms\Form\Type\SelectInputType;
use Wexample\SymfonyWex\Entity\Selection;

/**
 * A select whose options are selections, named by their title.
 *
 * Which selections are offered stays the caller's: that depends on the app
 * being looked at, and no field knows it.
 */
class SelectionChoiceType extends AbstractType
{
    public function getParent(): string
    {
        return SelectInputType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // The labels are what a human called these selections, so there is
            // nothing to look up in the translations.
            'auto_translate_choices' => false,
            'choice_value' => static fn (?Selection $selection): string => $selection?->getId()->toRfc4122() ?? '',
            'choice_label' => static fn (?Selection $selection): string => (string) $selection?->getTitle(),
        ]);
    }
}
