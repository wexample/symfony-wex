<?php

namespace Wexample\SymfonyWex\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wexample\SymfonyForms\Form\AbstractForm;
use Wexample\SymfonyForms\Form\Type\TextareaInputType;
use Wexample\SymfonyWex\Entity\App;

class AppForm extends AbstractForm
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => App::class,
        ]);
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        // Everything else on an App is read from its `.wex` directory and
        // rewritten on the next scan; the description is the one thing a human
        // owns.
        $builder
            ->add(
                'description',
                TextareaInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => false,
                ]
            );

        $this->builderAddSubmit($builder);
    }
}
