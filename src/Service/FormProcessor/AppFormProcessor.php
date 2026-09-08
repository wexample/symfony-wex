<?php

namespace Wexample\SymfonyWex\Service\FormProcessor;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wexample\SymfonyForms\Service\FormProcessor\AbstractFormProcessor;
use Wexample\SymfonyWex\Entity\App;
use Wexample\SymfonyWex\Service\WorkdirProjector;
use Wexample\SymfonyWex\Service\WorkdirWriter;

/**
 * Saves an app where an app is saved: in the `.wex` directory it carries.
 *
 * Persisting the row instead would save nothing — it holds what the last read
 * of that directory said, and the next one overwrites it. So the file is
 * written first, and the row is read back from it rather than kept.
 */
class AppFormProcessor extends AbstractFormProcessor
{
    public function __construct(
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        private readonly WorkdirWriter $writer,
        private readonly WorkdirProjector $projector,
    ) {
        parent::__construct($formFactory, $requestStack, $urlGenerator);
    }

    public function onValid(FormInterface $form): void
    {
        /** @var App $app */
        $app = $form->getData();

        $this->writer->write(
            $app->getPath(),
            ['global' => ['description' => $app->getDescription()]]
        );

        // What the page shows next is what the next scan would find, so a value
        // the file refuses never looks accepted.
        $this->projector->projectOne($app->getPath());

        $this->setSuccessAction(['type' => self::ACTION_DEFAULT]);
        $this->setNotification('@form::success.message');
    }
}
