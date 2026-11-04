<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\ViewHelpers;

use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Resolves the configured placeholder image (setting t3bootstrap-blog.placeholderImage)
 * to a File object, or null when the setting is empty or points to nothing.
 *
 * A mistyped path must not take the whole list page down, so lookup errors are
 * swallowed here instead of surfacing as an exception from the picture ViewHelper.
 */
final class PlaceholderImageViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('path', 'string', 'EXT:… path, public path or FAL identifier (1:/…)', false, '');
    }

    public function render(): ?File
    {
        $path = trim((string)$this->arguments['path']);
        if ($path === '') {
            return null;
        }
        try {
            $image = GeneralUtility::makeInstance(ImageService::class)->getImage($path, null, false);
        } catch (\Exception) {
            return null;
        }
        return $image instanceof File ? $image : null;
    }
}
