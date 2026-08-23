<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Hooks;

use TYPO3\CMS\Core\Hooks\CreateSiteConfiguration as CoreCreateSiteConfiguration;
use WapplerSystems\Blog\Constants;

class CreateSiteConfigurationHook extends CoreCreateSiteConfiguration
{
    protected $allowedPageTypes = [
        Constants::DOKTYPE_BLOG_PAGE
    ];
}
