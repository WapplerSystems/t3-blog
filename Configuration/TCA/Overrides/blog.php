<?php

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$GLOBALS['TCA']['pages']['columns']['featured_image']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = [

    'blog_detail' => [
        'title' => 'Detailansicht',
        'allowedAspectRatios' => [
            'default' => [
                'title' => '16:9',
                'value' => 16 / 9
            ],
        ],
    ],
    'blog_preview_1' => [
        'title' => 'Quadratische Vorschau',
        'allowedAspectRatios' => [
            'default' => [
                'title' => '1:1',
                'value' => 1
            ],
        ],
    ],
    'blog_preview_2' => [
        'title' => 'Längliche Vorschau',
        'allowedAspectRatios' => [
            'default' => [
                'title' => '2:1',
                'value' => 2/1
            ],
        ],
    ],
    'blog_preview_3' => [
        'title' => 'Hochkant Vorschau',
        'allowedAspectRatios' => [
            'default' => [
                'title' => '1:2',
                'value' => 1/2
            ],
        ],
    ],

];

$GLOBALS['TCA']['pages']['columns']['featured_image']['config']['overrideChildTca']['types'] = [
    '0' => [
        'showitem' => '
            --palette--;;imageoverlayPalette,
            --palette--;;filePalette'
    ],
    \TYPO3\CMS\Core\Resource\FileType::IMAGE->value => [
        'showitem' => '
            --palette--;;imageoverlayPalette,
            --palette--;;filePalette'
    ],

];

ExtensionManagementUtility::addToAllTCAtypes('tx_blog_domain_model_comment', 'author', '', 'after:hidden');
