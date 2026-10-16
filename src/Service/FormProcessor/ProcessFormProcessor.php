<?php

namespace Wexample\SymfonyWex\Service\FormProcessor;

use RuntimeException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wexample\SymfonyForms\Service\FormProcessor\AbstractFormProcessor;
use Wexample\SymfonyWex\Entity\Process;
use Wexample\SymfonyWex\Interface\AutoCommitSettingsInterface;
use Wexample\SymfonyWex\Service\AgentServerClient;
use Wexample\SymfonyWex\Service\ProcessRecordProjector;

/**
 * Saves a process where a process is saved: the record file it was read from,
 * inside the app it belongs to.
 *
 * Persisting the row instead would save nothing — it holds what the last read
 * of that file said, and the next one overwrites it. So wex writes the file
 * first — and commits it when auto commit is on — and the row is read back
 * from it rather than kept.
 */
class ProcessFormProcessor extends AbstractFormProcessor
{
    public function __construct(
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        private readonly AgentServerClient $agentServer,
        private readonly AutoCommitSettingsInterface $settings,
        private readonly ProcessRecordProjector $projector,
    ) {
        parent::__construct($formFactory, $requestStack, $urlGenerator);
    }

    public function onValid(FormInterface $form): void
    {
        /** @var Process $process */
        $process = $form->getData();
        $app = $process->getApp() ?? throw new RuntimeException('The process belongs to no mounted app.');

        // Written and, when auto commit is on, committed by wex itself.
        $this->agentServer->command(
            'process::process/update',
            $app->getPath(),
            array_filter([
                'id' => $process->getId()->toRfc4122(),
                'title' => $process->getTitle(),
                'type' => $process->getType(),
                'selection' => $process->getSelection()?->getId()->toRfc4122(),
                // The form edits JSON text; the record holds the values it stands for.
                'options' => json_encode((object) $process->getOptionValues(), JSON_THROW_ON_ERROR),
                'severity' => $process->getSeverity(),
            ], static fn (?string $value): bool => null !== $value),
            $this->settings->autoCommit(),
            $this->settings->commitAuthor($app),
        );

        $this->projector->projectOne($process);

        $this->setSuccessAction(['type' => self::ACTION_DEFAULT]);
        $this->setNotification('@form::success.message');
    }
}
