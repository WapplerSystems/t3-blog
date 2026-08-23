<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

return [
    \WapplerSystems\Blog\Domain\Model\Content::class => [
        'tableName' => 'tt_content',
    ],
    \WapplerSystems\Blog\Domain\Model\Post::class => [
        'tableName' => 'pages',
    ],
    \WapplerSystems\Blog\Domain\Model\Category::class => [
        'tableName' => 'sys_category',
    ],
    \WapplerSystems\Blog\Domain\Model\FrontendUser::class => [
        'tableName' => 'fe_users',
    ],
    \WapplerSystems\Blog\Domain\Model\Comment::class => [
        'tableName' => 'tx_blog_domain_model_comment',
        'properties' => [
            'post' => [
                'fieldName' => 'parentid'
            ],
        ],
    ],
    \WapplerSystems\Blog\Domain\Model\Tag::class => [
        'tableName' => 'tx_blog_domain_model_tag',
    ],
    \WapplerSystems\Blog\Domain\Model\Author::class => [
        'tableName' => 'tx_blog_domain_model_author',
    ],
];
