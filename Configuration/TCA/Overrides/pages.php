<?php

$GLOBALS['TCA']['pages']['columns']['featured_image']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants']['preview'] = [
    'title' => 'Preview Image',
    'allowedAspectRatios' => [
        'NaN' => [
            'title' => 'Frei',
            'value' => 0.0
        ],
        '16:9' => [
            'title' => '16:9',
            'value' => 16 / 9
        ],
        '22:7' => [
            'title' => '22:7',
            'value' => 22 / 7
        ],
    ],
    'selectedRatio' => '22:7',
];
