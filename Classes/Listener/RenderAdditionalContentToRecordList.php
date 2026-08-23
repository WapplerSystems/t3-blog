<?php

declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Listener;

use TYPO3\CMS\Backend\Controller\Event\RenderAdditionalContentToRecordListEvent;
use WapplerSystems\Blog\Backend\View\BlogPostHeaderContentRenderer;

class RenderAdditionalContentToRecordList
{
    protected BlogPostHeaderContentRenderer $blogPostHeaderContentRenderer;

    public function __construct(BlogPostHeaderContentRenderer $blogPostHeaderContentRenderer)
    {
        $this->blogPostHeaderContentRenderer = $blogPostHeaderContentRenderer;
    }

    /**
     * @return void
     */
    public function __invoke(RenderAdditionalContentToRecordListEvent $event)
    {
        $request = $event->getRequest();
        $content = $this->blogPostHeaderContentRenderer->render($request);
        $event->addContentAbove($content);
    }
}
