<?php

namespace Wexample\SymfonyWex\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wexample\SymfonyForms\Form\Type\SelectInputType;
use Wexample\SymfonyWex\Entity\ProcessType;
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
     * What a type reads as in the list: its label, then its name.
     *
     * The label alone is not enough. A generic family names its types after
     * the option they carry — `Name`, `Mode` — which says nothing of where
     * they come from, and two families may well use the same word: the name
     * is what tells them apart, and what the record will hold.
     */
    public static function label(ProcessType $processType): string
    {
        return $processType->getLabel().' — '.$processType->getName();
    }

    /**
     * @return array<string, string> label to name
     */
    private function choices(): array
    {
        $choices = [];

        foreach ($this->processTypes->findAllByName() as $processType) {
            $choices[self::label($processType)] = $processType->getName();
        }

        return $choices;
    }
}
