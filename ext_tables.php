<?php

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\DataHandling\PageDoktypeRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\Blog\Constants;

if (!defined('TYPO3')) {
    die('Access denied.');
}

$dokTypeRegistry = GeneralUtility::makeInstance(PageDoktypeRegistry::class);
$dokTypeRegistry->add(Constants::DOKTYPE_BLOG_POST, ['allowedTables' => '*']);
$dokTypeRegistry->add(Constants::DOKTYPE_BLOG_PAGE, ['allowedTables' => '*']);
