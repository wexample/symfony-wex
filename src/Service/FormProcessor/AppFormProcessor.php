<?php

namespace Wexample\SymfonyWex\Service\FormProcessor;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wexample\SymfonyForms\Service\FormProcessor\AbstractFormProcessor;
use Wexample\SymfonyWex\Form\AppForm;

class AppFormProcessor extends AbstractFormProcessor
{
    public function __construct(
        FormFactoryInterface $formFactory,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct($formFactory, $requestStack, $urlGenerator);
    }

    // The convention behind guessFormClass() only reaches App\Form\; a form
    // shipped in a bundle names its class.
    public static function getFormClass(): string
    {
        return AppForm::class;
    }

    public function onValid(FormInterface $form): void
    {
        $this->entityManager->persist($form->getData());
        $this->entityManager->flush();

        $this->setSuccessAction(['type' => self::ACTION_DEFAULT]);
        $this->setNotification('@form::success.message');
    }
}
