<?php
declare(strict_types = 1);

namespace T3Bootstrap\Blog\Domain\Finisher;

use T3G\AgencyPack\Blog\Domain\Model\Comment;
use T3G\AgencyPack\Blog\Domain\Repository\CommentRepository;
use T3G\AgencyPack\Blog\Domain\Repository\PostRepository;
use T3G\AgencyPack\Blog\Notification\CommentAddedNotification;
use T3G\AgencyPack\Blog\Notification\NotificationManager;
use T3G\AgencyPack\Blog\Service\CacheService;
use T3G\AgencyPack\Blog\Service\CommentService;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

/**
 * This finisher redirects to another Controller.
 *
 * Scope: frontend
 */
class CommentFormFinisher extends AbstractFinisher
{
    protected static $messages = [
        CommentService::STATE_ERROR => [
            'title' => 'message.addComment.error.title',
            'text' => 'message.addComment.error.text',
            'severity' => FlashMessage::ERROR,
        ],
        CommentService::STATE_MODERATION => [
            'title' => 'message.addComment.moderation.title',
            'text' => 'message.addComment.moderation.text',
            'severity' => FlashMessage::INFO,
        ],
        CommentService::STATE_SUCCESS => [
            'title' => 'message.addComment.success.title',
            'text' => 'message.addComment.success.text',
            'severity' => FlashMessage::OK,
        ],
    ];

    protected function executeInternal()
    {
        $configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);
        $settings = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'blog');
        $postRepository = $this->objectManager->get(PostRepository::class);
        $commentRepository = $this->objectManager->get(CommentRepository::class);
        $cacheService = $this->objectManager->get(CacheService::class);
        $commentService = $this->objectManager->get(CommentService::class);
        $commentService->injectSettings($settings['comments']);

        $frontendUserRepository = GeneralUtility::makeInstance(FrontendUserRepository::class);
        $context = GeneralUtility::makeInstance(Context::class);
        $feId = $context->getPropertyFromAspect('frontend.user', 'id');
        $frontendUser = $frontendUserRepository->findByUid($feId);


        // Create Comment
        $values = $this->finisherContext->getFormValues();
        $comment = new Comment();
        $comment->setAuthor($frontendUser);
        $comment->setComment($values['comment'] ?? '');
        //$commentRepository->add($comment);
        $post = $postRepository->findCurrentPost();
        $state = $commentService->addComment($post, $comment);


        // Add FlashMessage
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            LocalizationUtility::translate(self::$messages[$state]['text'], 'blog'),
            LocalizationUtility::translate(self::$messages[$state]['title'], 'blog'),
            self::$messages[$state]['severity'],
            true
        );
        $this->finisherContext->getControllerContext()->getFlashMessageQueue()->addMessage($flashMessage);

        if ($state !== CommentService::STATE_ERROR) {
            $comment->setCrdate(new \DateTime());
            GeneralUtility::makeInstance(NotificationManager::class)
                ->notify(GeneralUtility::makeInstance(CommentAddedNotification::class, '', '', [
                    'comment' => $comment,
                    'post' => $post,
                ]));
            $cacheService->flushCacheByTag('tx_blog_post_' . $post->getUid());
        }
    }
}
