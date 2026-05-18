<?php

declare(strict_types=1);

return [
    \T3Bootstrap\Blog\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users',
    ],
    \T3Bootstrap\Blog\Domain\Model\Comment::class => [
        'tableName' => 'tx_blog_domain_model_comment',
        'properties' => [
            'post' => [
                'fieldName' => 'parentid',
            ],
        ],
    ],
];