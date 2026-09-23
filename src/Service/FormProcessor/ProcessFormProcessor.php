<?php

namespace Wexample\SymfonyWex\Service\FormProcessor;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wexample\SymfonyForms\Service\FormProcessor\AbstractFormProcessor;
use Wexample\SymfonyWex\Entity\Process;
use Wexample\SymfonyWex\Service\ProcessHydrator;
use Wexample\SymfonyWex\Service\ProcessRecordProjector;
use Wexample\SymfonyWex\Service\WorkdirDataWriter;

/**
 * Saves a process where a process is saved: the record file it was read from,
 * inside the app it belongs to.
 *
 * Persisting the row instead would save nothing — it holds what the last read
 * of that file said, and the next one overwrites it. So the file is written
 * first, and the row is read back from it rather than kept.
 */
class ProcessFormProcessor extends AbstractFormProcessor
{
    public function __construct(
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        private readonly WorkdirDataWriter $writer,
        private readonly ProcessRecordProjector $projector,
    ) {
        parent::__construct($formFactory, $requestStack, $urlGenerator);
    }

    public function onValid(FormInterface $form): void
    {
        /** @var Process $process */
        $process = $form->getData();

        $this->writer->write($process->getPath(), [
            ProcessHydrator::KEY_TITLE => $process->getTitle(),
            ProcessHydrator::KEY_TYPE => $process->getType(),
            ProcessHydrator::KEY_SELECTION_ID => $process->getSelection()?->getId()->toRfc4122(),
            // The form edits a YAML block; the record holds the values it stands for.
            ProcessHydrator::KEY_OPTIONS => $process->getOptionValues(),
        ]);

        $this->projector->projectOne($process);

        $this->setSuccessAction(['type' => self::ACTION_DEFAULT]);
        $this->setNotification('@form::success.message');
    }
}
