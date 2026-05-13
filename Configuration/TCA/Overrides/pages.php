<?php
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use T3G\AgencyPack\Blog\Constants;

// --- Custom fields for blog posts ---
$newPagesColumns = [

    'time_to_read' => [
        'label' => 'Dauer zum Lesen',
        'config' => [
            'type' => 'input',
            'eval' => 'int',
            'default' => '0',
        ],
    ],
    'intro' => [
        'label' => 'Intro',
        'config' => [
            'type' => 'text',
            'enableRichtext' => true,
        ],
    ],

];

ExtensionManagementUtility::addTCAcolumns('pages', $newPagesColumns);

ExtensionManagementUtility::addToAllTCAtypes('pages', 'time_to_read,intro',
    (string)Constants::DOKTYPE_BLOG_POST, 'after:subtitle');


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
