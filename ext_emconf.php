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
    'version' => '13.2.1',
    'constraints' => [
        'depends' => [
            'typo3' => '13.0.0-13.4.99',
            't3b_core' => '13.0.0-13.99.99',
            'blog' => '13.0.0',
        ],
    ],
];

