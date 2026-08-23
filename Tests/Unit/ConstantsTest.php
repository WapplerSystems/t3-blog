<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use WapplerSystems\Blog\Constants;

class ConstantsTest extends UnitTestCase
{
    #[Test]
    public function constantForDoktypeOfBlogPostsIsSetCorrectly(): void
    {
        self::assertEquals(137, Constants::DOKTYPE_BLOG_POST);
        self::assertEquals(138, Constants::DOKTYPE_BLOG_PAGE);
    }
}
