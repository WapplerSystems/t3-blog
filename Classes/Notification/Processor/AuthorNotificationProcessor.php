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
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Mime\Part\TextPart;
use TYPO3\CMS\Core\Mail\MailerInterface;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use WapplerSystems\Blog\Domain\Model\Author;
use WapplerSystems\Blog\Domain\Model\Post;
use WapplerSystems\Blog\Notification\CommentAddedNotification;
use WapplerSystems\Blog\Notification\NotificationInterface;

#[Autoconfigure(public: true)]
readonly class AuthorNotificationProcessor implements ProcessorInterface
{
    public function __construct(private MailerInterface $mailer)
    {
    }

    public function process(ServerRequestInterface $request, NotificationInterface $notification): void
    {
        $notificationId = $notification->getNotificationId();

        if ($notificationId === CommentAddedNotification::class) {
            $this->processCommentAddNotification($request, $notification);
        }
    }

    protected function processCommentAddNotification(ServerRequestInterface $request, NotificationInterface $notification): void
    {
        $settings = $request->getAttribute('site')->getSettings();

        /** @var Post $post */
        $post = $notification->getData()['post'];
        if ($settings->get('plugin.tx_blog.settings.notifications.CommentAddedNotification.author.enable') ?? false) {
            /** @var Author $author */
            foreach ($post->getAuthors() as $author) {
                $mail = GeneralUtility::makeInstance(MailMessage::class);
                $mail
                    ->setSubject($notification->getTitle())
                    ->setBody(new TextPart($notification->getMessage(), 'utf-8', 'html'))
                    ->setFrom([$settings->get('plugin.tx_blog.settings.notifications.email.senderMail')])
                    ->setTo([$author->getEmail()]);
                $this->mailer->send($mail);
            }
        }
    }
}
