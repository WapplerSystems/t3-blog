<?php

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

$addColumnArray = [
    'blog_comments' => [
        'exclude' => 0,
        'label' => 'LLL:EXT:ws_blog/Resources/Private/Language/locallang.xlf:fe_users.blog_comments',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_blog_domain_model_comment',
            'foreign_field' => 'author',
            'appearance' => [
                'enabledControls' => [
                    'info' => false,
                    'new' => false,
                    'dragdrop' => false,
                    'sort' => false,
                    'hide' => false,
                    'delete' => false,
                    'localize' => false,
                ],
            ],
        ]
    ],
];
ExtensionManagementUtility::addTCAcolumns('fe_users', $addColumnArray);

ExtensionManagementUtility::addToAllTCAtypes('fe_users', '--div--;Blog,blog_comments');
