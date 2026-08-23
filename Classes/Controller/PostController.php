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
use Psr\Http\Message\ServerRequestInterface;
use GeorgRinger\NumberedPagination\NumberedPagination;
use WapplerSystems\Blog\Domain\Model\Author;
use WapplerSystems\Blog\Domain\Model\Category;
use WapplerSystems\Blog\Domain\Model\Post;
use WapplerSystems\Blog\Domain\Model\Tag;
use WapplerSystems\Blog\Domain\Repository\AuthorRepository;
use WapplerSystems\Blog\Domain\Repository\CategoryRepository;
use WapplerSystems\Blog\Domain\Repository\PostRepository;
use WapplerSystems\Blog\Domain\Repository\TagRepository;
use WapplerSystems\Blog\Factory\PostRepositoryDemandFactory;
use WapplerSystems\Blog\Pagination\BlogPagination;
use WapplerSystems\Blog\Service\CacheService;
use WapplerSystems\Blog\Pagination\QueryResultPaginator;
use TYPO3\CMS\Core\Pagination\PaginationInterface;
use TYPO3\CMS\Core\Pagination\PaginatorInterface;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use WapplerSystems\Blog\Service\MetaTagService;
use WapplerSystems\Blog\Utility\ArchiveUtility;
use WapplerSystems\Blog\Utility\Socials\MastodonUtility;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3Fluid\Fluid\View\ViewInterface;

class PostController extends ActionController
{
    protected PostRepository $postRepository;
    protected AuthorRepository $authorRepository;
    protected CategoryRepository $categoryRepository;
    protected TagRepository $tagRepository;
    protected CacheService $blogCacheService;
    protected PostRepositoryDemandFactory $postRepositoryDemandFactory;

    public function __construct(
        PostRepository $postRepository,
        AuthorRepository $authorRepository,
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        CacheService $blogCacheService,
        PostRepositoryDemandFactory $postRepositoryDemandFactory
    ) {
        $this->postRepository = $postRepository;
        $this->authorRepository = $authorRepository;
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
        $this->blogCacheService = $blogCacheService;
        $this->postRepositoryDemandFactory = $postRepositoryDemandFactory;
    }

    /**
     * @param ViewInterface $view
     */
    protected function initializeView($view): void
    {
        if ($this->request->getFormat() === 'rss') {
            $action = '.' . $this->request->getControllerActionName();
            $arguments = [];
            switch ($action) {
                case '.listPostsByCategory':
                    if (isset($this->arguments['category'])) {
                        $arguments[] = $this->arguments['category']->getValue()->getTitle();
                    }
                    break;
                case '.listPostsByDate':
                    $arguments[] = (int)$this->arguments['year']->getValue();
                    if (isset($this->arguments['month'])) {
                        $arguments[] = (int)$this->arguments['month']->getValue();
                    }
                    break;
                case '.listPostsByTag':
                    if (isset($this->arguments['tag'])) {
                        $arguments[] = $this->arguments['tag']->getValue()->getTitle();
                    }
                    break;
                case '.listPostsByAuthor':
                    if (isset($this->arguments['author'])) {
                        $arguments[] = $this->arguments['author']->getValue()->getName();
                    }
                    break;
                default:
            }

            $title = '' !== ($this->settings['rss']['title'] ?? '')
                ? $this->settings['rss']['title']
                : LocalizationUtility::translate('feed.title' . $action, 'blog', $arguments);
            $description = '' !== ($this->settings['rss']['description'] ?? '')
                ? $this->settings['rss']['description']
                : LocalizationUtility::translate('feed.description' . $action, 'blog', $arguments);

            $feedData = [
                'title' => $title,
                'description' => $description,
                'language' => $this->getSiteLanguage()->getLocale()->getLanguageCode(),
                'link' => $this->getRequestUrl(),
                'date' => date('r'),
            ];
            $this->view->assign('feed', $feedData);
        }

        $contentObject = $this->request->getAttribute('currentContentObject');
        $this->view->assign('data', $contentObject !== null ? $contentObject->data : null);
    }

    /**
     * Show a list of recent posts.
     */
    public function listRecentPostsAction(int $currentPage = 1): ResponseInterface
    {
        if ($this->request->getFormat() === 'rss') {
            $maximumItems = (int) ($this->settings['rss']['maximumDisplayedItems'] ?? 10);
            $this->view->assign('type', 'recent');
            $this->view->assign('posts', $this->postRepository->findAllWithLimit($maximumItems));
            $this->view->assign('pagination', null);

            return $this->htmlResponse();
        }

        $maximumItems = (int) ($this->settings['lists']['posts']['maximumDisplayedItems'] ?? 0);
        $posts = (0 === $maximumItems)
            ? $this->postRepository->findAll()
            : $this->postRepository->findAllWithLimit($maximumItems);

        $paginationConfiguration = $this->settings['lists']['pagination'] ?? [];
        $itemsPerPage = (int) (($paginationConfiguration['itemsPerPage'] ?? '') ?: 12);
        $maximumNumberOfLinks = (int) ($paginationConfiguration['maximumNumberOfLinks'] ?? 0);

        $paginator = GeneralUtility::makeInstance(
            QueryResultPaginator::class,
            $posts,
            $currentPage,
            $itemsPerPage,
            (int) ($this->settings['limit'] ?? 0),
            (int) ($this->settings['offset'] ?? 0)
        );
        $pagination = $this->getPaginationInstance(
            $paginationConfiguration['class'] ?? BlogPagination::class,
            $maximumNumberOfLinks,
            $paginator
        );

        $ajaxPageType = $this->getAjaxPageType('recent');
        $previousPageAjaxUri = '';
        if ($pagination->getPreviousPageNumber() && ($pagination->getPreviousPageNumber() >= $pagination->getFirstPageNumber())) {
            $previousPageAjaxUri = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)
                ->setTargetPageType($ajaxPageType)
                ->uriFor('listRecentPosts', ['currentPage' => $currentPage - 1], 'Post', 'blog', 'Posts');
        }

        $nextPageAjaxUri = '';
        if ($pagination->getNextPageNumber() && ($pagination->getNextPageNumber() <= $pagination->getLastPageNumber())) {
            $nextPageAjaxUri = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)
                ->setTargetPageType($ajaxPageType)
                ->uriFor('listRecentPosts', ['currentPage' => $currentPage + 1], 'Post', 'blog', 'Posts');
        }

        $this->view->assignMultiple([
            'settings' => $this->settings,
            'type' => 'recent',
            'posts' => $posts,
            'nextPage' => $currentPage + 1,
            'previousPage' => $currentPage - 1,
            'currentPage' => $currentPage,
            'paginator' => $paginator,
            'pagination' => $pagination,
            'previousPageAjaxUri' => $previousPageAjaxUri,
            'nextPageAjaxUri' => $nextPageAjaxUri,
        ]);

        return $this->htmlResponse();
    }

    /**
     * Show a list of posts for a selected category.
     */
    public function listByDemandAction(): ResponseInterface
    {
        $repositoryDemand = $this->postRepositoryDemandFactory->createFromSettings($this->settings['demand'] ?? []);

        $this->view->assign('type', 'demand');
        $this->view->assign('demand', $repositoryDemand);
        $this->view->assign('posts', $this->postRepository->findByRepositoryDemand($repositoryDemand));
        $this->view->assign('pagination', []);
        return $this->htmlResponse();
    }

    /**
     * Show a number of latest posts.
     */
    public function listLatestPostsAction(): ResponseInterface
    {
        if ($this->request->getFormat() === 'rss') {
            $maximumItems = (int) ($this->settings['rss']['maximumDisplayedItems'] ?? 10);
        } else {
            $maximumItems = (int) ($this->settings['latestPosts']['limit'] ?? 3);
        }
        $posts = $this->postRepository->findAllWithLimit($maximumItems);

        $this->view->assign('type', 'latest');
        $this->view->assign('posts', $posts);
        return $this->htmlResponse();
    }

    public function listPostsByDateAction(?int $year = null, ?int $month = null, int $currentPage = 1): ResponseInterface
    {
        if ($year === null) {
            $posts = $this->postRepository->findMonthsAndYearsWithPosts();
            $this->view->assign('archiveData', ArchiveUtility::extractDataFromPosts($posts));
        } else {
            $dateTime = new \DateTimeImmutable(sprintf('%d-%d-1', $year, $month ?? 1));
            if ($this->request->getFormat() === 'rss') {
                $maximumItems = (int) ($this->settings['rss']['maximumDisplayedItems'] ?? 10);
            }
            $posts = $this->postRepository->findByMonthAndYearWithLimit($year, $month, $maximumItems ?? 0);
            if ($this->request->getFormat() !== 'rss') {
                $pagination = $this->getPagination($posts, $currentPage);
            }
            $this->view->assign('type', 'bydate');
            $this->view->assign('month', $month);
            $this->view->assign('year', $year);
            $this->view->assign('timestamp', $dateTime->getTimestamp());
            $this->view->assign('posts', $posts);
            $this->view->assign('pagination', $pagination ?? null);
            $title = str_replace([
                '###MONTH###',
                '###MONTH_NAME###',
                '###YEAR###',
            ], [
                (string) $month,
                $dateTime->format('F'),
                (string) $year,
            ], (string) LocalizationUtility::translate('meta.title.listPostsByDate', 'blog'));
            MetaTagService::set(MetaTagService::META_TITLE, (string) $title);
            MetaTagService::set(MetaTagService::META_DESCRIPTION, (string) LocalizationUtility::translate('meta.description.listPostsByDate', 'blog'));
        }
        return $this->htmlResponse();
    }

    /**
     * Show a list of posts by given category.
     */
    public function listPostsByCategoryAction(?Category $category = null, int $currentPage = 1): ResponseInterface
    {
        if ($category === null) {
            $contentObject = $this->request->getAttribute('currentContentObject');
            $referenceUid = $contentObject !== null ? (int) $contentObject->data['uid'] : null;
            if ($referenceUid !== null) {
                $categories = $this->categoryRepository->getByReference('tt_content', $referenceUid);
                if ($categories !== null && $categories->count() > 0) {
                    /** @var ?Category $category */
                    $category = $categories->getFirst();
                }
            }
        }

        if ($category === null) {
            $this->view->assign('categories', $this->categoryRepository->findAll());

            return $this->htmlResponse();
        }

        if ($this->request->getFormat() === 'rss') {
            $maximumItems = (int) ($this->settings['rss']['maximumDisplayedItems'] ?? 10);
            $this->view->assign('type', 'bycategory');
            $this->view->assign('posts', $this->postRepository->findAllByCategoryWithLimit($category, $maximumItems));
            $this->view->assign('pagination', null);
            $this->view->assign('category', $category);
            MetaTagService::set(MetaTagService::META_TITLE, (string) $category->getTitle());
            MetaTagService::set(MetaTagService::META_DESCRIPTION, (string) $category->getDescription());

            return $this->htmlResponse();
        }

        $posts = $this->postRepository->findAllByCategory($category);

        $paginationConfiguration = $this->settings['lists']['pagination'] ?? [];
        $itemsPerPage = (int) (($paginationConfiguration['itemsPerPage'] ?? '') ?: 12);
        $maximumNumberOfLinks = (int) ($paginationConfiguration['maximumNumberOfLinks'] ?? 0);

        $paginator = GeneralUtility::makeInstance(
            QueryResultPaginator::class,
            $posts,
            $currentPage,
            $itemsPerPage,
            (int) ($this->settings['limit'] ?? 0),
            (int) ($this->settings['offset'] ?? 0)
        );
        $pagination = $this->getPaginationInstance(
            $paginationConfiguration['class'] ?? SimplePagination::class,
            $maximumNumberOfLinks,
            $paginator
        );

        $ajaxPageType = $this->getAjaxPageType('category');
        $ajaxArguments = ['tx_blog_category[category]' => $category->getUid()];
        $previousPageAjaxUri = '';
        if ($pagination->getPreviousPageNumber() && ($pagination->getPreviousPageNumber() >= $pagination->getFirstPageNumber())) {
            $previousPageAjaxUri = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)
                ->setTargetPageType($ajaxPageType)
                ->setArguments($ajaxArguments)
                ->uriFor('listPostsByCategory', ['currentPage' => $currentPage - 1], 'Post', 'blog', 'Category');
        }

        $nextPageAjaxUri = '';
        if ($pagination->getNextPageNumber() && ($pagination->getNextPageNumber() <= $pagination->getLastPageNumber())) {
            $nextPageAjaxUri = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)
                ->setTargetPageType($ajaxPageType)
                ->setArguments($ajaxArguments)
                ->uriFor('listPostsByCategory', ['currentPage' => $currentPage + 1], 'Post', 'blog', 'Category');
        }

        $this->view->assignMultiple([
            'settings' => $this->settings,
            'type' => 'bycategory',
            'posts' => $posts,
            'category' => $category,
            'nextPage' => $currentPage + 1,
            'previousPage' => $currentPage - 1,
            // Die verschachtelte Form stammt aus t3bootstrap_blog; die Bootstrap-Partials
            // lesen pagination.paginator und pagination.pagination.
            'pagination' => [
                'currentPage' => $currentPage,
                'paginator' => $paginator,
                'pagination' => $pagination,
            ],
            'previousPageAjaxUri' => $previousPageAjaxUri,
            'nextPageAjaxUri' => $nextPageAjaxUri,
        ]);

        MetaTagService::set(MetaTagService::META_TITLE, (string) $category->getTitle());
        MetaTagService::set(MetaTagService::META_DESCRIPTION, (string) $category->getDescription());

        return $this->htmlResponse();
    }

    /**
     * Show a list of posts by given author.
     */
    public function listPostsByAuthorAction(?Author $author = null, int $currentPage = 1): ResponseInterface
    {
        if ($author !== null) {
            if ($this->request->getFormat() === 'rss') {
                $maximumItems = (int) ($this->settings['rss']['maximumDisplayedItems'] ?? 10);
            }
            $posts = $this->postRepository->findAllByAuthorWithLimit($author, $maximumItems ?? 0);
            if ($this->request->getFormat() !== 'rss') {
                $pagination = $this->getPagination($posts, $currentPage);
            }
            $this->view->assign('type', 'byauthor');
            $this->view->assign('posts', $posts);
            $this->view->assign('pagination', $pagination ?? null);
            $this->view->assign('author', $author);
            MetaTagService::set(MetaTagService::META_TITLE, (string) $author->getName());
            MetaTagService::set(MetaTagService::META_DESCRIPTION, (string) $author->getBio());
        } else {
            $this->view->assign('authors', $this->authorRepository->findAll());
        }
        return $this->htmlResponse();
    }

    /**
     * Show a list of posts by given tag.
     */
    public function listPostsByTagAction(?Tag $tag = null, int $currentPage = 1): ResponseInterface
    {
        if ($tag === null) {
            $this->view->assign('tags', $this->tagRepository->findAll());

            return $this->htmlResponse();
        }

        if ($this->request->getFormat() === 'rss') {
            $maximumItems = (int) ($this->settings['rss']['maximumDisplayedItems'] ?? 10);
            $this->view->assign('type', 'bytag');
            $this->view->assign('posts', $this->postRepository->findAllByTagWithLimit($tag, $maximumItems));
            $this->view->assign('pagination', null);
            $this->view->assign('tag', $tag);
            MetaTagService::set(MetaTagService::META_TITLE, (string) $tag->getTitle());
            MetaTagService::set(MetaTagService::META_DESCRIPTION, (string) $tag->getDescription());

            return $this->htmlResponse();
        }

        $posts = $this->postRepository->findAllByTag($tag);

        $paginationConfiguration = $this->settings['lists']['pagination'] ?? [];
        $itemsPerPage = (int) (($paginationConfiguration['itemsPerPage'] ?? '') ?: 12);
        $maximumNumberOfLinks = (int) ($paginationConfiguration['maximumNumberOfLinks'] ?? 0);

        $paginator = GeneralUtility::makeInstance(
            QueryResultPaginator::class,
            $posts,
            $currentPage,
            $itemsPerPage,
            (int) ($this->settings['limit'] ?? 0),
            (int) ($this->settings['offset'] ?? 0)
        );
        $pagination = $this->getPaginationInstance(
            $paginationConfiguration['class'] ?? SimplePagination::class,
            $maximumNumberOfLinks,
            $paginator
        );

        $ajaxPageType = $this->getAjaxPageType('tag');
        $ajaxArguments = ['tx_blog_tag[tag]' => $tag->getUid()];
        $previousPageAjaxUri = '';
        if ($pagination->getPreviousPageNumber() && ($pagination->getPreviousPageNumber() >= $pagination->getFirstPageNumber())) {
            $previousPageAjaxUri = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)
                ->setTargetPageType($ajaxPageType)
                ->setArguments($ajaxArguments)
                ->uriFor('listPostsByTag', ['currentPage' => $currentPage - 1], 'Post', 'blog', 'Tag');
        }

        $nextPageAjaxUri = '';
        if ($pagination->getNextPageNumber() && ($pagination->getNextPageNumber() <= $pagination->getLastPageNumber())) {
            $nextPageAjaxUri = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)
                ->setTargetPageType($ajaxPageType)
                ->setArguments($ajaxArguments)
                ->uriFor('listPostsByTag', ['currentPage' => $currentPage + 1], 'Post', 'blog', 'Tag');
        }

        $this->view->assignMultiple([
            'settings' => $this->settings,
            'type' => 'bytag',
            'posts' => $posts,
            'tag' => $tag,
            'nextPage' => $currentPage + 1,
            'previousPage' => $currentPage - 1,
            'pagination' => [
                'currentPage' => $currentPage,
                'paginator' => $paginator,
                'pagination' => $pagination,
            ],
            'previousPageAjaxUri' => $previousPageAjaxUri,
            'nextPageAjaxUri' => $nextPageAjaxUri,
        ]);

        MetaTagService::set(MetaTagService::META_TITLE, (string) $tag->getTitle());
        MetaTagService::set(MetaTagService::META_DESCRIPTION, (string) $tag->getDescription());

        return $this->htmlResponse();
    }

    /**
     * Sidebar action.
     */
    public function sidebarAction(): ResponseInterface
    {
        return $this->htmlResponse();
    }

    /**
     * Header action: output the header of blog post.
     */
    public function headerAction(): ResponseInterface
    {
        $post = $this->postRepository->findCurrentPost();
        $this->view->assign('post', $post);
        if ($post instanceof Post) {
            $this->blogCacheService->addTagsForPost($this->request, $post);
            $this->setFediverseCreatorMetaTag($post);
        }
        return $this->htmlResponse();
    }

    /**
     * Footer action: output the footer of blog post.
     */
    public function footerAction(): ResponseInterface
    {
        $post = $this->postRepository->findCurrentPost();
        $this->view->assign('post', $post);
        if ($post instanceof Post) {
            $this->blogCacheService->addTagsForPost($this->request, $post);
        }
        return $this->htmlResponse();
    }

    /**
     * Authors action: output author information of blog post.
     */
    public function authorsAction(): ResponseInterface
    {
        $post = $this->postRepository->findCurrentPost();
        $this->view->assign('post', $post);
        if ($post instanceof Post) {
            $this->blogCacheService->addTagsForPost($this->request, $post);
        }
        return $this->htmlResponse();
    }

    /**
     * Related posts action: show related posts based on the current post
     */
    public function relatedPostsAction(): ResponseInterface
    {
        $post = $this->postRepository->findCurrentPost();
        $posts = $this->postRepository->findRelatedPosts(
            (int)$this->settings['relatedPosts']['categoryMultiplier'],
            (int)$this->settings['relatedPosts']['tagMultiplier'],
            (int)$this->settings['relatedPosts']['limit']
        );
        $this->view->assign('type', 'related');
        $this->view->assign('post', $post);
        $this->view->assign('posts', $posts);
        return $this->htmlResponse();
    }

    private function getRequest(): ServerRequestInterface
    {
        return $GLOBALS['TYPO3_REQUEST'];
    }

    private function getSiteLanguage(): SiteLanguage
    {
        return $this->getRequest()->getAttribute('language');
    }

    private function getRequestUrl(): string
    {
        /** @var NormalizedParams $normalizedParams */
        $normalizedParams = $this->getRequest()->getAttribute('normalizedParams');
        return $normalizedParams->getRequestUrl();
    }

    private function setFediverseCreatorMetaTag(Post $post): void
    {
        foreach ($post->getAuthors() as $author) {
            $mastodonHandle = MastodonUtility::getMastodonHandle($author->getMastodon());
            if ($mastodonHandle !== '') {
                MetaTagService::set(MetaTagService::META_FEDIVERSE_CREATOR, $mastodonHandle);
            }
        }
    }

    /**
     * Die Ajax-Nachladeseiten sind PAGE-Objekte des Sets (typeNum 74385/74386/74387).
     * Ueber die Settings ueberschreibbar, damit ein Projekt eigene Typen vergeben kann.
     */
    protected function getAjaxPageType(string $listType): int
    {
        $defaults = ['recent' => 74385, 'category' => 74386, 'tag' => 74387];

        return (int) ($this->settings['ajax']['pageTypes'][$listType] ?? $defaults[$listType] ?? 0);
    }

    /**
     * Baut die Pagination zum konfigurierten Klassennamen; faellt auf SimplePagination
     * zurueck, wenn die konfigurierte Klasse fehlt.
     */
    protected function getPaginationInstance(string $paginationClass, int $maximumNumberOfLinks, PaginatorInterface $paginator): PaginationInterface
    {
        if ($maximumNumberOfLinks && $paginationClass === NumberedPagination::class && class_exists(NumberedPagination::class)) {
            return GeneralUtility::makeInstance(NumberedPagination::class, $paginator, $maximumNumberOfLinks);
        }

        if (class_exists($paginationClass)) {
            return GeneralUtility::makeInstance($paginationClass, $paginator);
        }

        return GeneralUtility::makeInstance(SimplePagination::class, $paginator);
    }

    protected function getPagination(QueryResultInterface $objects, int $currentPage = 1): ?BlogPagination
    {
        $maximumNumberOfLinks = (int) ($this->settings['lists']['pagination']['maximumNumberOfLinks'] ?? 0);
        $itemsPerPage = 10;
        if ($this->request->getFormat() === 'html') {
            $itemsPerPage = (int) ($this->settings['lists']['pagination']['itemsPerPage'] ?? 10);
        }

        $paginator = new QueryResultPaginator($objects, $currentPage, $itemsPerPage);
        return new BlogPagination($paginator, $maximumNumberOfLinks);
    }
}
