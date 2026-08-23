<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Controller;

use Psr\Http\Message\ResponseInterface;
use WapplerSystems\Blog\Domain\Model\Post;
use WapplerSystems\Blog\Domain\Repository\PostRepository;
use WapplerSystems\Blog\Service\CacheService;
use WapplerSystems\Blog\Service\CommentService;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class CommentController extends ActionController
{
    protected PostRepository $postRepository;
    protected CommentService $commentService;
    protected CacheService $cacheService;

    public function __construct(
        PostRepository $postRepository,
        CommentService $commentService,
        CacheService $cacheService
    ) {
        $this->postRepository = $postRepository;
        $this->commentService = $commentService;
        $this->cacheService = $cacheService;
    }

    /**
     * Show comment form.
     */
    public function formAction(): ResponseInterface
    {
        $this->view->assign('post', $this->postRepository->findCurrentPost());
        return $this->htmlResponse();
    }

    public function commentsAction(): ResponseInterface
    {
        $post = $this->postRepository->findCurrentPost();
        if ($post instanceof Post) {
            $comments = $this->commentService->getCommentsByPost($post);
            foreach ($comments as $comment) {
                $this->cacheService->addTagToPage($this->request, 'tx_blog_comment_' . $comment->getUid());
            }
            $this->view->assign('comments', $comments);
            $this->view->assign('post', $post);
        }
        return $this->htmlResponse();
    }
}
