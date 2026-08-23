<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Domain\Factory;

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use WapplerSystems\Blog\Domain\Finisher\FrontendUserCommentFormFinisher;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Validator\NotEmptyValidator;
use TYPO3\CMS\Extbase\Validation\Validator\StringLengthValidator;
use TYPO3\CMS\Form\Domain\Configuration\ConfigurationService;
use TYPO3\CMS\Form\Domain\Factory\AbstractFormFactory;
use TYPO3\CMS\Form\Domain\Finishers\RedirectFinisher;
use TYPO3\CMS\Form\Domain\Model\FormDefinition;

#[Autoconfigure(public: true, shared: false)]
class FrontendUserCommentFormFactory extends AbstractFormFactory
{
    /**
     * Build a FormDefinition.
     * This example build a FormDefinition manually,
     * so $configuration and $prototypeName are unused.
     *
     * @param array $configuration
     * @param string|null $prototypeName
     * @param ServerRequestInterface|null $request
     * @return FormDefinition
     */
    public function build(array $configuration, ?string $prototypeName = null, ?ServerRequestInterface $request = null): FormDefinition
    {
        $prototypeName = 'standard';
        $formConfigurationService = GeneralUtility::makeInstance(ConfigurationService::class);
        $prototypeConfiguration = $formConfigurationService->getPrototypeConfiguration($prototypeName);
        $prototypeConfiguration['formElementsDefinition']['BlogGoogleCaptcha'] = $prototypeConfiguration['formElementsDefinition']['BlogGoogleCaptcha'] ?? [];

        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $blogSettings = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'blog');

        $form = GeneralUtility::makeInstance(FormDefinition::class, 'postcomment', $prototypeConfiguration);
        $form->setRenderingOption('controllerAction', 'form');
        $form->setRenderingOption('submitButtonLabel', LocalizationUtility::translate('form.comment.submit', 'ws_blog'));
        $renderingOptions = $form->getRenderingOptions();
        $renderingOptions['partialRootPaths'][-1634043971] = 'EXT:ws_blog/Resources/Private/Partials/Form/';
        $form->setRenderingOption('partialRootPaths', $renderingOptions['partialRootPaths']);

        $page = $form->createPage('commentform');

        $commentField = $page->createElement('comment', 'Textarea');
        $commentField->setLabel(LocalizationUtility::translate('form.comment.comment', 'ws_blog'));
        $commentField->addValidator(GeneralUtility::makeInstance(NotEmptyValidator::class));

        $stringValidator = GeneralUtility::makeInstance(StringLengthValidator::class);
        $stringValidator->setOptions(['minimum' => 5]);
        $commentField->addValidator($stringValidator);

        $explanationText = $page->createElement('explanation', 'StaticText');
        $explanationText->setProperty('text', LocalizationUtility::translate('label.required.field', 'ws_blog') . ' ' . LocalizationUtility::translate('label.required.field.explanation', 'ws_blog'));

        $context = GeneralUtility::makeInstance(Context::class);
        $userIsLoggedIn = $context->getPropertyFromAspect('frontend.user', 'isLoggedIn');
        if ($userIsLoggedIn) {
            // Finisher
            $commentFinisher = GeneralUtility::makeInstance(FrontendUserCommentFormFinisher::class);
            if (method_exists($commentFinisher, 'setFinisherIdentifier')) {
                $commentFinisher->setFinisherIdentifier(FrontendUserCommentFormFinisher::class);
            }
            $form->addFinisher($commentFinisher);

            $redirectFinisher = GeneralUtility::makeInstance(RedirectFinisher::class);
            if (method_exists($redirectFinisher, 'setFinisherIdentifier')) {
                $redirectFinisher->setFinisherIdentifier(RedirectFinisher::class);
            }
            $request = $GLOBALS['TYPO3_REQUEST'] ?? null;
            $pageId = $request?->getAttribute('frontend.page.information')?->getId() ?? 0;
            $redirectFinisher->setOption('pageUid', (string)$pageId);
            $form->addFinisher($redirectFinisher);
        }


        $this->triggerFormBuildingFinished($form);
        return $form;
    }
}
