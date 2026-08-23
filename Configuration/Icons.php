<?php

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

// Provide icon for page tree, list view, ... :
return array_map(static fn (string $source) => ['provider' => SvgIconProvider::class, 'source' => $source], [
    'module-blog' => 'EXT:ws_blog/Resources/Public/Icons/module-blog.svg',
    'module-blog-posts' => 'EXT:ws_blog/Resources/Public/Icons/module-blog-posts.svg',
    'module-blog-comments' => 'EXT:ws_blog/Resources/Public/Icons/module-blog-comments.svg',
    'module-blog-setup' => 'EXT:ws_blog/Resources/Public/Icons/module-blog-setup.svg',
    'plugin-blog-archive' => 'EXT:ws_blog/Resources/Public/Icons/plugin-blog-archive.svg',
    'plugin-blog-authorposts' => 'EXT:ws_blog/Resources/Public/Icons/plugin-blog-authorposts.svg',
    'plugin-blog-authors' => 'EXT:ws_blog/Resources/Public/Icons/plugin-blog-authors.svg',
    'plugin-blog-category' => 'EXT:ws_blog/Resources/Public/Icons/plugin-blog-category.svg',
    'plugin-blog-commentform' => 'EXT:ws_blog/Resources/Public/Icons/plugin-blog-commentform.svg',
    'plugin-blog-comments' => 'EXT:ws_blog/Resources/Public/Icons/plugin-blog-comments.svg',
    'plugin-blog-demandedposts' => 'EXT:ws_blog/Resources/Public/Icons/plugin-blog-demandedposts.svg',
    'plugin-blog-header' => 'EXT:ws_blog/Resources/Public/Icons/plugin-blog-header.svg',
    'plugin-blog-footer' => 'EXT:ws_blog/Resources/Public/Icons/plugin-blog-footer.svg',
    'plugin-blog-posts' => 'EXT:ws_blog/Resources/Public/Icons/plugin-blog-posts.svg',
    'plugin-blog-relatedposts' => 'EXT:ws_blog/Resources/Public/Icons/plugin-blog-relatedposts.svg',
    'plugin-blog-sidebar' => 'EXT:ws_blog/Resources/Public/Icons/plugin-blog-sidebar.svg',
    'plugin-blog-tag' => 'EXT:ws_blog/Resources/Public/Icons/plugin-blog-tag.svg',
    'record-blog-author' => 'EXT:ws_blog/Resources/Public/Icons/record-blog-author.svg',
    'record-blog-comment' => 'EXT:ws_blog/Resources/Public/Icons/record-blog-comment.svg',
    'record-blog-page' => 'EXT:ws_blog/Resources/Public/Icons/record-blog-page.svg',
    'record-blog-page-root' => 'EXT:ws_blog/Resources/Public/Icons/record-blog-page-root.svg',
    'record-blog-post' => 'EXT:ws_blog/Resources/Public/Icons/record-blog-post.svg',
    'record-blog-tag' => 'EXT:ws_blog/Resources/Public/Icons/record-blog-tag.svg',
    'record-blog-category' => 'EXT:ws_blog/Resources/Public/Icons/record-blog-category.svg',
    'record-folder-contains-blog' => 'EXT:ws_blog/Resources/Public/Icons/record-folder-contains-blog.svg',
]);
