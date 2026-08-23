<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Service\Avatar;

use Psr\Http\Message\UriInterface;
use WapplerSystems\Blog\DataTransferObject\AvatarResource;

interface AvatarResourceResolverInterface
{
    public function resolve(UriInterface $uri): AvatarResource;
}
