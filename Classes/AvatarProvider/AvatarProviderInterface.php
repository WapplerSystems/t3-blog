<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\AvatarProvider;

use WapplerSystems\Blog\Domain\Model\Author;

interface AvatarProviderInterface
{
    public function getAvatarUrl(Author $author, int $size): string;
}
