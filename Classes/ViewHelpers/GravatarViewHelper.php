<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\ViewHelpers;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use WapplerSystems\Blog\AvatarProvider\GravatarProvider;
use WapplerSystems\Blog\Domain\Model\Author;

class GravatarViewHelper extends AbstractTagBasedViewHelper
{
    public function __construct()
    {
        $this->tagName = 'img';
        parent::__construct();
    }

    public function initializeArguments(): void
    {
        parent::initializeArguments();

        $this->registerArgument('email', 'string', 'The email address to resolve the gravatar for', true);
        $this->registerArgument('size', 'int', 'The size of the gravatar, ranging from 1 to 512', false, 64);
    }

    public function render(): string
    {
        $author = (new Author())->setEmail($this->arguments['email']);
        $size = (int)$this->arguments['size'];

        /** @var GravatarProvider $gravatarProvider */
        $gravatarProvider = GeneralUtility::makeInstance(GravatarProvider::class);
        $src = $gravatarProvider->getAvatarUrl($author, $size);

        $this->tag->addAttribute('src', (string) $src);
        $this->tag->addAttribute('width', (string) $size);
        $this->tag->addAttribute('height', (string) $size);

        return $this->tag->render();
    }
}
