<?php

namespace Wexample\SymfonyWex\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wexample\SymfonyForms\Form\AbstractForm;
use Wexample\SymfonyForms\Form\Type\TextareaInputType;
use Wexample\SymfonyForms\Form\Type\TextInputType;
use Wexample\SymfonyWex\Entity\Selection;

class SelectionForm extends AbstractForm
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Selection::class,
        ]);
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        // Both of them are a human's: what the selection is called, and what it
        // takes. Everything else on the record says when it was written.
        $builder
            ->add(
                'title',
                TextInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => true,
                    'help' => true,
                ]
            )
            ->add(
                'patterns',
                TextareaInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => false,
                    'help' => true,
                ]
            );

        $this->builderAddSubmit($builder);
    }
}
