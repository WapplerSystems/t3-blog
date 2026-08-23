<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\DataTransferObject;

use Psr\Http\Message\UriInterface;

class Gravatar implements AvatarResource
{
    private UriInterface $uri;
    private string $contentType;
    private string $content;

    public function __construct(UriInterface $uri, string $contentType, string $content)
    {
        $this->uri = $uri;
        $this->contentType = $contentType;
        $this->content = $content;
    }

    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
