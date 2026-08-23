<?php

declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Listener;

use TYPO3\CMS\Backend\Controller\Event\ModifyPageLayoutContentEvent;
use WapplerSystems\Blog\Backend\View\BlogPostHeaderContentRenderer;

class ModifyPageLayoutContent
{
    protected BlogPostHeaderContentRenderer $blogPostHeaderContentRenderer;

    public function __construct(BlogPostHeaderContentRenderer $blogPostHeaderContentRenderer)
    {
        $this->blogPostHeaderContentRenderer = $blogPostHeaderContentRenderer;
    }

    public function __invoke(ModifyPageLayoutContentEvent $event): void
    {
        $request = $event->getRequest();
        $content = $this->blogPostHeaderContentRenderer->render($request);
        $event->addHeaderContent($content);
    }
}
