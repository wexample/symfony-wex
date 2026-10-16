<?php

namespace Wexample\SymfonyWex\Service\FormProcessor;

use RuntimeException;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wexample\SymfonyForms\Service\FormProcessor\AbstractFormProcessor;
use Wexample\SymfonyWex\Entity\Selection;
use Wexample\SymfonyWex\Interface\AutoCommitSettingsInterface;
use Wexample\SymfonyWex\Service\AgentServerClient;
use Wexample\SymfonyWex\Service\SelectionRecordProjector;

/**
 * Saves a selection where a selection is saved: the record file it was read
 * from, inside the app it belongs to.
 *
 * Persisting the row instead would save nothing — it holds what the last read
 * of that file said, and the next one overwrites it. So wex writes the file
 * first — and commits it when auto commit is on — and the row is read back
 * from it rather than kept.
 */
class SelectionFormProcessor extends AbstractFormProcessor
{
    public function __construct(
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        private readonly AgentServerClient $agentServer,
        private readonly AutoCommitSettingsInterface $settings,
        private readonly SelectionRecordProjector $projector,
    ) {
        parent::__construct($formFactory, $requestStack, $urlGenerator);
    }

    public function onValid(FormInterface $form): void
    {
        /** @var Selection $selection */
        $selection = $form->getData();
        $app = $selection->getApp() ?? throw new RuntimeException('The selection belongs to no mounted app.');

        // Written and, when auto commit is on, committed by wex itself.
        $this->agentServer->command(
            'process::selection/update',
            $app->getPath(),
            [
                'id' => $selection->getId()->toRfc4122(),
                'title' => (string) $selection->getTitle(),
                'patterns' => $selection->getPatterns(),
            ],
            $this->settings->autoCommit(),
            $this->settings->commitAuthor($app),
        );

        $this->projector->projectOne($selection);

        $this->setSuccessAction(['type' => self::ACTION_DEFAULT]);
        $this->setNotification('@form::success.message');
    }
}
