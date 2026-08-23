<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Notification;

interface NotificationInterface
{
    public function getNotificationId(): string;
    public function getTitle(): string;
    public function getMessage(): string;
    public function getData(): array;
}
