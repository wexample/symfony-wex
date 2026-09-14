<?php

namespace Wexample\SymfonyWex\Controller\Pages\Entity;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyForms\Attribute\EntityFormProcessor;
use Wexample\SymfonyLoader\Controller\AbstractEntityPagesController;
use Wexample\SymfonyWex\Entity\Selection;
use Wexample\SymfonyWex\Entity\Traits\Manipulator\SelectionEntityManipulatorTrait;
use Wexample\SymfonyWex\Service\FormProcessor\SelectionFormProcessor;
use Wexample\SymfonyWex\Traits\SymfonyWexBundleClassTrait;

#[Route(path: 'selection/', name: 'entity_selection_')]
class SelectionController extends AbstractEntityPagesController
{
    use SelectionEntityManipulatorTrait;
    use SymfonyWexBundleClassTrait;

    final public const string ROUTE_EDIT = self::DEFAULT_ROUTE_NAME_EDIT;

    #[EntityFormProcessor(SelectionFormProcessor::class, Selection::class)]
    #[Route(name: self::ROUTE_EDIT, path: '{id}/'.self::ROUTE_EDIT, options: self::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function edit(
        FormInterface $selectionForm
    ): Response {
        return $this->renderPage(self::ROUTE_EDIT, [
            'form_edit' => $selectionForm->createView(),
        ]);
    }
}
