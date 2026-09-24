<?php

namespace Wexample\SymfonyWex\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Json;
use Wexample\SymfonyForms\Form\AbstractForm;
use Wexample\SymfonyForms\Form\Type\SelectInputType;
use Wexample\SymfonyForms\Form\Type\TextareaInputType;
use Wexample\SymfonyForms\Form\Type\TextInputType;
use Wexample\SymfonyWex\Entity\Process;
use Wexample\SymfonyWex\Entity\Selection;
use Wexample\SymfonyWex\Form\Type\ProcessTypeChoiceType;
use Wexample\SymfonyWex\Form\Type\SelectionChoiceType;
use Wexample\SymfonyWex\Repository\ProcessTypeRepository;
use Wexample\SymfonyWex\Repository\SelectionRepository;

class ProcessForm extends AbstractForm
{
    public function __construct(
        private readonly SelectionRepository $selections,
        private readonly ProcessTypeRepository $types,
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
                'options',
                TextareaInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => false,
                    'help' => true,
                    // Left empty, the field submits null; the entity says an
                    // absence of options as an empty block, so the form does too.
                    'empty_data' => '',
                    // Refused here rather than read as nothing: a typo would
                    // otherwise run the process with no options at all.
                    'constraints' => [new Json()],
                ]
            )
            ->add(
                'severity',
                SelectInputType::class,
                [
                    self::FIELD_OPTION_NAME_LABEL => true,
                    self::FIELD_OPTION_NAME_REQUIRED => true,
                    'help' => true,
                    'choices' => Process::SEVERITIES,
                ]
            );

        // Both fields are added once there is a process to read them from: the
        // selections offered are the ones of the app it belongs to, and the
        // types offered have to hold the one it already names.
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event): void {
                /** @var Process $process */
                $process = $event->getData();

                $event->getForm()->add(
                    'type',
                    ProcessTypeChoiceType::class,
                    [
                        self::FIELD_OPTION_NAME_LABEL => true,
                        self::FIELD_OPTION_NAME_REQUIRED => true,
                        'help' => true,
                        'choices' => $this->typeChoices($process->getType()),
                    ]
                );

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
     * The treatments to choose from, the one already named among them.
     *
     * An addon can leave while a process still names what it brought: the
     * choice is kept so the process stays editable, rather than answering with
     * a field refusing the value it holds.
     *
     * @return array<string, string> label to name
     */
    private function typeChoices(string $current): array
    {
        $choices = [];

        foreach ($this->types->findAllByName() as $processType) {
            $choices[ProcessTypeChoiceType::label($processType)] = $processType->getName();
        }

        if ('' !== $current && ! in_array($current, $choices, true)) {
            $choices[$current] = $current;
        }

        return $choices;
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
