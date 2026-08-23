<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\ViewHelpers;

use Psr\Http\Message\ServerRequestInterface;
use WapplerSystems\Blog\Domain\Model\Post;
use WapplerSystems\Blog\Service\CacheService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class CacheViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('post', Post::class, 'the post to tag', true);
    }

    public function render(): string
    {
        if (null === $this->renderingContext) {
            throw new \RuntimeException('CacheViewHelper requires an existing rendering context.', 1781701008);
        }
        $post = $this->arguments['post'];
        $request = $this->renderingContext->getAttribute(ServerRequestInterface::class);
        GeneralUtility::makeInstance(CacheService::class)->addTagsForPost($request, $post);

        return '';
    }
}
