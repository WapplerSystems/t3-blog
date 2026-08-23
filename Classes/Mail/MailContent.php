<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Mail;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;
use TYPO3\CMS\Core\View\ViewInterface;

class MailContent
{
    public function __construct(
        protected readonly ViewFactoryInterface $viewFactory
    ) {
    }

    public function render(ServerRequestInterface $request, string $template, array $arguments): string
    {
        $view = $this->getTemplateObject($request);
        $view->assignMultiple($arguments);

        return $view->render($template);
    }

    protected function getTemplateObject(ServerRequestInterface $request): ViewInterface
    {
        $settings = $request->getAttribute('site')->getSettings();

        return $this->viewFactory->create(new ViewFactoryData(
            templateRootPaths: [
                'EXT:ws_blog/Resources/Private/Mails/Templates/',
                $settings->get('plugin.tx_blog.view.emails.templateRootPath')
            ],
            partialRootPaths: [
                'EXT:ws_blog/Resources/Private/Mails/Partials/',
                $settings->get('plugin.tx_blog.view.emails.partialRootPath')

            ],
            layoutRootPaths: [
                'EXT:ws_blog/Resources/Private/Mails/Layouts/',
                $settings->get('plugin.tx_blog.view.emails.layoutRootPath')
            ],
            request: $request,
        ));
    }
}
