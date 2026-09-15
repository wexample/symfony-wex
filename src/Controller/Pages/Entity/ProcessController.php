<?php

namespace Wexample\SymfonyWex\Controller\Pages\Entity;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyForms\Attribute\EntityFormProcessor;
use Wexample\SymfonyLoader\Controller\AbstractEntityPagesController;
use Wexample\SymfonyWex\Entity\Process;
use Wexample\SymfonyWex\Entity\Traits\Manipulator\ProcessEntityManipulatorTrait;
use Wexample\SymfonyWex\Service\FormProcessor\ProcessFormProcessor;
use Wexample\SymfonyWex\Traits\SymfonyWexBundleClassTrait;

#[Route(path: 'process/', name: 'entity_process_')]
class ProcessController extends AbstractEntityPagesController
{
    use ProcessEntityManipulatorTrait;
    use SymfonyWexBundleClassTrait;

    final public const string ROUTE_EDIT = self::DEFAULT_ROUTE_NAME_EDIT;

    #[EntityFormProcessor(ProcessFormProcessor::class, Process::class)]
    #[Route(name: self::ROUTE_EDIT, path: '{id}/'.self::ROUTE_EDIT, options: self::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function edit(
        FormInterface $processForm
    ): Response {
        return $this->renderPage(self::ROUTE_EDIT, [
            'form_edit' => $processForm->createView(),
        ]);
    }
}
