<?php

namespace Wexample\SymfonyWex\Service\FormProcessor;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wexample\SymfonyForms\Service\FormProcessor\AbstractFormProcessor;
use Wexample\SymfonyWex\Entity\App;
use Wexample\SymfonyWex\Interface\AutoCommitSettingsInterface;
use Wexample\SymfonyWex\Service\AgentServerClient;
use Wexample\SymfonyWex\Service\WorkdirProjector;

/**
 * Saves an app where an app is saved: in the `.wex` directory it carries.
 *
 * Persisting the row instead would save nothing — it holds what the last read
 * of that directory said, and the next one overwrites it. So wex writes the
 * file first — and commits it when auto commit is on — and the row is read
 * back from it rather than kept.
 */
class AppFormProcessor extends AbstractFormProcessor
{
    public function __construct(
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        private readonly AgentServerClient $agentServer,
        private readonly AutoCommitSettingsInterface $settings,
        private readonly WorkdirProjector $projector,
    ) {
        parent::__construct($formFactory, $requestStack, $urlGenerator);
    }

    public function onValid(FormInterface $form): void
    {
        /** @var App $app */
        $app = $form->getData();

        // Written and, when auto commit is on, committed by wex itself.
        $this->agentServer->command(
            'app::config/set',
            $app->getPath(),
            ['key' => 'global.description', 'value' => (string) $app->getDescription()],
            $this->settings->autoCommit(),
            $this->settings->commitAuthor($app),
        );

        // What the page shows next is what the next scan would find, so a value
        // the file refuses never looks accepted.
        $this->projector->projectOne($app->getPath());

        $this->setSuccessAction(['type' => self::ACTION_DEFAULT]);
        $this->setNotification('@form::success.message');
    }
}
