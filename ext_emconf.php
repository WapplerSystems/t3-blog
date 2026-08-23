<?php

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Blog (WapplerSystems)',
    'description' => 'Fork von t3g/blog mit eingefalteter t3bootstrap-Bruecke: Bootstrap-5-Rendering, Kommentare fuer eingeloggte Frontend-Benutzer, getrennte Ablageordner je Datensatztyp.',
    'category' => 'fe',
    'state' => 'stable',
    'author' => 'Sven Wappler',
    'author_email' => 'typo3@wappler.systems',
    'author_company' => 'WapplerSystems',
    'version' => '14.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '14.3.0-14.99.99',
            'form' => '14.3.0-14.99.99',
            't3b_core' => '',
        ],
        'conflicts' => [
            'blog' => '',
            't3bootstrap_blog' => '',
        ],
        'suggests' => [],
    ],
];
