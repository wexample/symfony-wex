<?php

namespace Wexample\SymfonyWex\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wexample\SymfonyForms\Form\Type\SelectInputType;
use Wexample\SymfonyWex\Repository\ProcessTypeRepository;

/**
 * A select whose options are the treatments wex declares.
 *
 * The choices come from the projected rows and not from the caller, unlike the
 * selections: what a process may run is the same everywhere, only what it runs
 * on depends on the app. A type the board could offer but no worker could run
 * would be a promise nothing keeps, which is why the list has one source.
 */
class ProcessTypeChoiceType extends AbstractType
{
    public function __construct(
        private readonly ProcessTypeRepository $processTypes,
    ) {
    }

    public function getParent(): string
    {
        return SelectInputType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // A type carries its own label: whoever declares one has no way of
            // writing in the translations of the form that offers it.
            'auto_translate_choices' => false,
            'choices' => $this->choices(),
        ]);
    }

    /**
     * @return array<string, string> label to name
     */
    private function choices(): array
    {
        $choices = [];

        foreach ($this->processTypes->findAllByName() as $processType) {
            $choices[$processType->getLabel()] = $processType->getName();
        }

        return $choices;
    }
}
