<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Template patch für blog',
    'description' => 't3bootstrap modifications for the blog extension',
    'author' => 'Sven Wappler',
    'author_email' => 'typo3YYYY@wappler.systems',
    'category' => 'fe',
    'author_company' => 'WapplerSystems',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '14.1.3',
    'constraints' => [
        'depends' => [
            'typo3' => '14.0.0-14.99.99',
            't3b_core' => '',
            'blog' => '14.0.0',
        ],
    ],
];

