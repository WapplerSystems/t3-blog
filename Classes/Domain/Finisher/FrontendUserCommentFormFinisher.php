<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Domain\Finisher;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;
use WapplerSystems\Blog\Domain\Model\Comment;
use WapplerSystems\Blog\Domain\Repository\CommentRepository;
use WapplerSystems\Blog\Domain\Repository\FrontendUserRepository;
use WapplerSystems\Blog\Domain\Repository\PostRepository;
use WapplerSystems\Blog\Notification\CommentAddedNotification;
use WapplerSystems\Blog\Notification\NotificationManager;
use WapplerSystems\Blog\Service\CacheService;
use WapplerSystems\Blog\Service\CommentService;

/**
 *
 * Scope: frontend
 */
class FrontendUserCommentFormFinisher extends AbstractFinisher
{

    public function __construct(
        private PostRepository $postRepository,
        private CommentRepository $commentRepository,
        private CacheService $cacheService,
        private CommentService $commentService,
        private FrontendUserRepository $frontendUserRepository,
        private FlashMessageService $flashMessageService
    ) {

    }

    protected static $messages = [
        CommentService::STATE_ERROR => [
            'title' => 'message.addComment.error.title',
            'text' => 'message.addComment.error.text',
            'severity' =>  ContextualFeedbackSeverity::ERROR,
        ],
        CommentService::STATE_MODERATION => [
            'title' => 'message.addComment.moderation.title',
            'text' => 'message.addComment.moderation.text',
            'severity' => ContextualFeedbackSeverity::INFO,
        ],
        CommentService::STATE_SUCCESS => [
            'title' => 'message.addComment.success.title',
            'text' => 'message.addComment.success.text',
            'severity' => ContextualFeedbackSeverity::OK,
        ],
    ];

    protected function executeInternal()
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManagerInterface::class);
        $blogSettings = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS, 'blog');
        $this->commentService->setSettings($blogSettings['comments'] ?? []);

        $context = GeneralUtility::makeInstance(Context::class);
        $feId = (int)$context->getPropertyFromAspect('frontend.user', 'id');
        $frontendUser = $feId > 0 ? $this->frontendUserRepository->findByUid($feId) : null;

        // Create Comment
        $values = $this->finisherContext->getFormValues();
        $comment = new Comment();
        if ($frontendUser instanceof \WapplerSystems\Blog\Domain\Model\FrontendUser) {
            $comment->setAuthor($frontendUser);
            $comment->setName(trim($frontendUser->getFirstName() . ' ' . $frontendUser->getLastName()) ?: $frontendUser->getName() ?: $frontendUser->getUsername());
            $comment->setEmail($frontendUser->getEmail());
        }
        $comment->setComment($values['comment'] ?? '');
        //$commentRepository->add($comment);
        $post = $this->postRepository->findCurrentPost();
        if ($post === null) {
            throw new \RuntimeException('No post found for adding comment', 1676543210);
        }

        $state = $this->commentService->addComment($post, $comment);

        // Add FlashMessage
        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            LocalizationUtility::translate(self::$messages[$state]['text'], 'ws_blog'),
            LocalizationUtility::translate(self::$messages[$state]['title'], 'ws_blog'),
            self::$messages[$state]['severity'],
            true
        );

        $request = $this->finisherContext->getRequest();
        $pluginNamespace = 'tx_' . strtolower($request->getControllerExtensionName() ?: 'blog') . '_' . strtolower($request->getPluginName() ?: 'commentform');
        $this->flashMessageService
            ->getMessageQueueByIdentifier('extbase.flashmessages.' . $pluginNamespace)
            ->addMessage($flashMessage);

        if ($state !== CommentService::STATE_ERROR) {
            $comment->setCrdate(new \DateTime());
            GeneralUtility::makeInstance(NotificationManager::class)
                ->notify(
                    $request,
                    GeneralUtility::makeInstance(CommentAddedNotification::class, '', '', [
                        'comment' => $comment,
                        'post' => $post,
                    ])
                );
            $this->cacheService->flushCacheByTag('tx_blog_post_' . $post->getUid());
        }
    }
}
