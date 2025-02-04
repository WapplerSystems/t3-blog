<?php

$EM_CONF['t3bootstrap-news'] = [
    'title' => 'Template Patch für news',
    'description' => 'Adds the t3bootstrap TCA palettes to the news plugins',
    'author' => 'Sven Wappler',
    'author_email' => 'typo3YYYY@wappler.systems',
    'category' => 'fe',
    'author_company' => 'WapplerSystems',
    'state' => 'stable',
    'clearCacheOnLoad' => 1,
    'version' => '13.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '13.0.0-13.4.99',
            'ws_t3bootstrap' => '13.0.0-13.99.99',
            'news' => '12.0.0',
            'numbered_pagination' => '2.0.0'
        ],
    ],
];

