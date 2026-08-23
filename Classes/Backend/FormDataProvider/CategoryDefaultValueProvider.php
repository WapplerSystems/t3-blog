<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Backend\FormDataProvider;

use TYPO3\CMS\Backend\Form\FormDataProviderInterface;
use WapplerSystems\Blog\Constants;

class CategoryDefaultValueProvider implements FormDataProviderInterface
{
    public function addData(array $result): array
    {
        if ($result['command'] !== 'new' ||
            $result['tableName'] !== 'sys_category' ||
            $result['parentPageRow']['module'] !== 'blog') {
            return $result;
        }

        $result['databaseRow']['record_type'][0] = (string) Constants::CATEGORY_TYPE_BLOG;
        $result['recordTypeValue'] = (string) Constants::CATEGORY_TYPE_BLOG;

        return $result;
    }
}
