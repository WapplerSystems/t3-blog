<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Notification\Processor;

use Psr\Http\Message\ServerRequestInterface;
use WapplerSystems\Blog\Notification\NotificationInterface;

interface ProcessorInterface
{
    public function process(ServerRequestInterface $request, NotificationInterface $notification): void;
}
