<?php

namespace Wexample\SymfonyWex\Controller\Pages\Entity;

use Wexample\SymfonyWex\Entity\Traits\Manipulator\AppEntityManipulatorTrait;
use Symfony\Component\Routing\Attribute\Route;
use Wexample\SymfonyLoader\Controller\AbstractEntityPagesController;
use Wexample\SymfonyWex\Traits\SymfonyWexBundleClassTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Wexample\SymfonyForms\Attribute\EntityFormProcessor;
use Wexample\SymfonyWex\Entity\App;
use Wexample\SymfonyWex\Service\FormProcessor\AppFormProcessor;

#[Route(path: 'app/', name: 'entity_app_')]
class AppController extends AbstractEntityPagesController
{
    use AppEntityManipulatorTrait;
    use SymfonyWexBundleClassTrait;

    final public const string ROUTE_EDIT = self::DEFAULT_ROUTE_NAME_EDIT;

    #[EntityFormProcessor(AppFormProcessor::class, App::class)]
    #[Route(name: self::ROUTE_EDIT, path: '{id}/'.self::ROUTE_EDIT, options: self::ROUTE_OPTIONS_ONLY_EXPOSE)]
    public function edit(
        FormInterface $appForm
    ): Response {
        return $this->renderPage(self::ROUTE_EDIT, [
            'form_edit' => $appForm->createView(),
        ]);
    }
}
