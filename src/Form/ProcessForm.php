<?php

namespace Wexample\SymfonyWex\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Wexample\SymfonyForms\Form\AbstractForm;
use Wexample\SymfonyForms\Form\Type\TextareaInputType;
use Wexample\SymfonyForms\Form\Type\TextInputType;
use Wexample\SymfonyWex\Entity\Process;
use Wexample\SymfonyWex\Entity\Selection;
use Wexample\SymfonyWex\Form\Type\SelectionChoiceType;
use Wexample\SymfonyWex\Repository\SelectionRepository;

class ProcessForm extends AbstractForm
{
    public function __construct(
        private readonly SelectionRepository $selections,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => Process::class,
        ]);
    }

    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
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
                'type',
                TextInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => true,
                    'help' => true,
                ]
            )
            ->add(
                'options',
                TextareaInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => false,
                    'help' => true,
                ]
            );

        // The selections offered are the ones of the app this process belongs
        // to, and which app that is only the process being edited can say — so
        // the field is added once there is one.
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event): void {
                /** @var Process $process */
                $process = $event->getData();

                $event->getForm()->add(
                    'selection',
                    SelectionChoiceType::class,
                    [
                        self::FIELD_OPTION_NAME_LABEL => true,
                        self::FIELD_OPTION_NAME_REQUIRED => true,
                        'help' => true,
                        'choices' => $this->besideRecord($process->getPath()),
                    ]
                );
            }
        );

        $this->builderAddSubmit($builder);
    }

    /**
     * The selections declared beside that record.
     *
     * A process and the selections it may run on are records of the same app,
     * so they are siblings in its `.wex/data` — which is how the app is read
     * back from a record without being carried along.
     *
     * @return array<string, Selection>
     */
    private function besideRecord(string $path): array
    {
        $selections = [];

        foreach ($this->selections->findByPathPrefix(dirname($path, 2).'/selection') as $selection) {
            $selections[(string) $selection->getTitle()] = $selection;
        }

        return $selections;
    }
}
