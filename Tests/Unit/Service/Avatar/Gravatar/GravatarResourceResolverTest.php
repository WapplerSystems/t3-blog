<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Tests\Unit\Service\Avatar\Gravatar;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Http\Stream;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use WapplerSystems\Blog\Service\Avatar\Gravatar\GravatarResourceResolver;

class GravatarResourceResolverTest extends UnitTestCase
{
    public function testResolveReturnsProperResponse(): void
    {
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $response
            ->method('getStatusCode')
            ->willReturn(200);
        $response
            ->method('getBody')
            ->willReturn(new Stream('php://temp'));
        $response
            ->method('getHeaderLine')
            ->willReturn('image/jpeg');

        $client = $this->getMockBuilder(ClientInterface::class)->getMock();
        $client
            ->method('sendRequest')
            ->willReturn($response);

        $gravatarResourceResolver = new GravatarResourceResolver(
            $client,
            $this->getMockBuilder(RequestFactory::class)->disableOriginalConstructor()->getMock()
        );

        $url = 'https://www.gravatar.com/avatar/71803b16fcdb8ac77611d0a977b20164';
        $avatarResource = $gravatarResourceResolver->resolve(new Uri($url));

        self::assertSame($url, (string)$avatarResource->getUri());
        self::assertSame('image/jpeg', $avatarResource->getContentType());
        self::assertSame('', $avatarResource->getContent());
    }

    public function testResolveReturnsResponseWith404StatusCode(): void
    {
        $this->expectException(\RuntimeException::class);

        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $response
            ->method('getStatusCode')
            ->willReturn(404);
        $response
            ->method('getReasonPhrase')
            ->willReturn('Not Found');

        $client = $this->getMockBuilder(ClientInterface::class)->getMock();
        $client
            ->method('sendRequest')
            ->willReturn($response);

        $gravatarResourceResolver = new GravatarResourceResolver(
            $client,
            $this->getMockBuilder(RequestFactory::class)->disableOriginalConstructor()->getMock()
        );

        $url = 'https://www.gravatar.com/avatar/71803b16fcdb8ac77611d0a977b20164';
        $gravatarResourceResolver->resolve(new Uri($url));
    }

    public function testResolveReturnsResponseWithEmptyContentTypeHeader(): void
    {
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $response
            ->method('getStatusCode')
            ->willReturn(200);
        $response
            ->method('getBody')
            ->willReturn(new Stream('php://temp'));
        $response
            ->method('getHeaderLine')
            ->willReturn('');

        $client = $this->getMockBuilder(ClientInterface::class)->getMock();
        $client
            ->method('sendRequest')
            ->willReturn($response);

        $gravatarResourceResolver = new GravatarResourceResolver(
            $client,
            $this->getMockBuilder(RequestFactory::class)->disableOriginalConstructor()->getMock()
        );

        $url = 'https://www.gravatar.com/avatar/71803b16fcdb8ac77611d0a977b20164';
        $avatarResource = $gravatarResourceResolver->resolve(new Uri($url));

        self::assertSame($url, (string)$avatarResource->getUri());
        self::assertSame('text/plain', $avatarResource->getContentType());
        self::assertSame('', $avatarResource->getContent());
    }
}
