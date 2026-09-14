<?php

namespace Wexample\SymfonyWex\Service\FormProcessor;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wexample\SymfonyForms\Service\FormProcessor\AbstractFormProcessor;
use Wexample\SymfonyWex\Entity\Selection;
use Wexample\SymfonyWex\Service\SelectionHydrator;
use Wexample\SymfonyWex\Service\SelectionRecordProjector;
use Wexample\SymfonyWex\Service\WorkdirDataWriter;

/**
 * Saves a selection where a selection is saved: the record file it was read
 * from, inside the app it belongs to.
 *
 * Persisting the row instead would save nothing — it holds what the last read
 * of that file said, and the next one overwrites it. So the file is written
 * first, and the row is read back from it rather than kept.
 */
class SelectionFormProcessor extends AbstractFormProcessor
{
    public function __construct(
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        private readonly WorkdirDataWriter $writer,
        private readonly SelectionRecordProjector $projector,
    ) {
        parent::__construct($formFactory, $requestStack, $urlGenerator);
    }

    public function onValid(FormInterface $form): void
    {
        /** @var Selection $selection */
        $selection = $form->getData();

        $this->writer->write($selection->getPath(), [
            SelectionHydrator::KEY_TITLE => $selection->getTitle(),
            SelectionHydrator::KEY_PATTERNS => $selection->getPatterns(),
        ]);

        $this->projector->projectOne($selection);

        $this->setSuccessAction(['type' => self::ACTION_DEFAULT]);
        $this->setNotification('@form::success.message');
    }
}
