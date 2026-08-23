<?php


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
                    'info' => FALSE,
                    'new' => FALSE,
                    'dragdrop' => FALSE,
                    'sort' => FALSE,
                    'hide' => FALSE,
                    'delete' => FALSE,
                    'localize' => FALSE,
                ],
            ],
        ]
    ],
];
ExtensionManagementUtility::addTCAcolumns('fe_users', $addColumnArray);

ExtensionManagementUtility::addToAllTCAtypes('fe_users', '--div--;Blog,blog_comments');


