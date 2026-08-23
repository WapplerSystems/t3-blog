<?php

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Tests\Unit\Domain\Model;

use PHPUnit\Framework\Attributes\Test;
use WapplerSystems\Blog\Constants;
use WapplerSystems\Blog\Domain\Model\Post;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * Tests for domains model News
 *
 */
class PostTest extends UnitTestCase
{
    #[Test]
    public function doktypeEqualsConstant(): void
    {
        $post = new Post();
        self::assertEquals(Constants::DOKTYPE_BLOG_POST, $post->getDoktype());
    }
}
