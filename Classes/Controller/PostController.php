<?php
declare(strict_types = 1);

namespace T3Bootstrap\Blog\Controller;

use GeorgRinger\NumberedPagination\NumberedPagination;
use T3Bootstrap\Blog\Pagination\QueryResultPaginator;
use Psr\Http\Message\ResponseInterface;
use T3G\AgencyPack\Blog\Domain\Model\Category;
use T3G\AgencyPack\Blog\Domain\Model\Tag;
use T3G\AgencyPack\Blog\Service\MetaTagService;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

class PostController extends \T3G\AgencyPack\Blog\Controller\PostController
{



    /**
     * Show a list of recent posts.
     *
     * @param int $currentPage
     * @return ResponseInterface
     * @throws InvalidQueryException
     */
    public function listRecentPostsAction(int $currentPage = 1): ResponseInterface
    {
        $maximumItems = (int) ($this->settings['lists']['posts']['maximumDisplayedItems'] ?? 0);

        $posts = (0 === $maximumItems)
            ? $this->postRepository->findAll()
            : $this->postRepository->findAllWithLimit($maximumItems);

        $paginationConfiguration = $this->settings['lists']['pagination'] ?? [];
        $itemsPerPage = (int)(($paginationConfiguration['itemsPerPage'] ?? '') ?: 12);
        $maximumNumberOfLinks = (int)($paginationConfiguration['maximumNumberOfLinks'] ?? 0);

        $paginator = GeneralUtility::makeInstance(QueryResultPaginator::class, $posts, $currentPage, $itemsPerPage, (int)($this->settings['limit'] ?? null), (int)($this->settings['offset'] ?? 0));
        $paginationClass = $paginationConfiguration['class'] ?? SimplePagination::class;
        $pagination = $this->getPagination2($paginationClass, $maximumNumberOfLinks, $paginator);



        $previousPageAjaxUri = '';
        if ($pagination->getPreviousPageNumber() && ($pagination->getPreviousPageNumber() >= $pagination->getFirstPageNumber())) {
            $previousPageAjaxUri = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)
                ->setTargetPageType(74385)
                ->uriFor('listRecentPosts',[
                    'currentPage' => $currentPage - 1,
                ],
                    'Post', 'blog', 'Posts');
        }

        $nextPageAjaxUri = '';
        if ($pagination->getNextPageNumber() && ($pagination->getNextPageNumber() <= $pagination->getLastPageNumber())) {
            $nextPageAjaxUri = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)
                ->setTargetPageType(74385)
                ->uriFor('listRecentPosts',[
                    'currentPage' => $currentPage + 1,
                ],
                    'Post', 'blog', 'Posts');
        }

        $this->view->assignMultiple([
            'settings' => $this->settings,
            'nextPage' => $currentPage + 1,
            'previousPage' => $currentPage - 1,
            'pagination' => [
                'currentPage' => $currentPage,
                'paginator' => $paginator,
                'pagination' => $pagination,
            ],
            'previousPageAjaxUri' => $previousPageAjaxUri,
            'nextPageAjaxUri' => $nextPageAjaxUri,
            'type' => 'recent',
        ]);

        //$this->view->assign('pagination', $pagination);
        return $this->htmlResponse();
    }


    /**
     * @param $paginationClass
     * @param int $maximumNumberOfLinks
     * @param $paginator
     * @return \#o#Э#A#M#C\GeorgRinger\News\Controller\NewsController.getPagination.0|NumberedPagination|mixed|\Psr\Log\LoggerAwareInterface|string|SimplePagination|\TYPO3\CMS\Core\SingletonInterface
     */
    protected function getPagination2($paginationClass, int $maximumNumberOfLinks, $paginator)
    {
        if (class_exists(NumberedPagination::class) && $paginationClass === NumberedPagination::class && $maximumNumberOfLinks) {
            $pagination = GeneralUtility::makeInstance(NumberedPagination::class, $paginator, $maximumNumberOfLinks);
        } elseif (class_exists($paginationClass)) {
            $pagination = GeneralUtility::makeInstance($paginationClass, $paginator);
        } else {
            $pagination = GeneralUtility::makeInstance(SimplePagination::class, $paginator);
        }
        return $pagination;
    }


    /**
     * Show a list of posts by given category.
     *
     * @param Category|null $category
     * @param int $currentPage
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function listPostsByCategoryAction(?Category $category = null, int $currentPage = 1): ResponseInterface
    {
        if ($category === null) {
            $categories = $this->categoryRepository->getByReference(
                'tt_content',
                $this->configurationManager->getContentObject()->data['uid']
            );

            if (!empty($categories)) {
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $category = $categories->getFirst();
            }
        }


        if ($category) {
            $posts = $this->postRepository->findAllByCategory($category);

            $paginationConfiguration = $this->settings['lists']['pagination'] ?? [];
            $itemsPerPage = (int)(($paginationConfiguration['itemsPerPage'] ?? '') ?: 12);
            $maximumNumberOfLinks = (int)($paginationConfiguration['maximumNumberOfLinks'] ?? 0);

            $paginator = GeneralUtility::makeInstance(QueryResultPaginator::class, $posts, $currentPage, $itemsPerPage, (int)($this->settings['limit'] ?? null), (int)($this->settings['offset'] ?? 0));
            $paginationClass = $paginationConfiguration['class'] ?? SimplePagination::class;
            $pagination = $this->getPagination2($paginationClass, $maximumNumberOfLinks, $paginator);

            $previousPageAjaxUri = '';
            if ($pagination->getPreviousPageNumber() && ($pagination->getPreviousPageNumber() >= $pagination->getFirstPageNumber())) {
                $previousPageAjaxUri = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)
                    ->setTargetPageType(74386)
                    ->setArguments([
                        'tx_blog_category[category]' => $category->getUid(),
                    ])
                    ->uriFor('listPostsByCategory',[
                        'currentPage' => $currentPage - 1,
                    ],
                        'Post', 'blog', 'Category');
            }

            $nextPageAjaxUri = '';
            if ($pagination->getNextPageNumber() && ($pagination->getNextPageNumber() <= $pagination->getLastPageNumber())) {
                $nextPageAjaxUri = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)
                    ->setTargetPageType(74386)
                    ->setArguments([
                        'tx_blog_category[category]' => $category->getUid(),
                    ])
                    ->uriFor('listPostsByCategory',[
                        'currentPage' => $currentPage + 1,
                    ],
                        'Post', 'blog', 'Category');
            }

            $this->view->assignMultiple([
                'settings' => $this->settings,
                'nextPage' => $currentPage + 1,
                'previousPage' => $currentPage - 1,
                'pagination' => [
                    'currentPage' => $currentPage,
                    'paginator' => $paginator,
                    'pagination' => $pagination,
                ],
                'previousPageAjaxUri' => $previousPageAjaxUri,
                'nextPageAjaxUri' => $nextPageAjaxUri,
                'type' => 'bycategory',
                'category' => $category,
            ]);

            MetaTagService::set(MetaTagService::META_TITLE, (string) $category->getTitle());
            MetaTagService::set(MetaTagService::META_DESCRIPTION, (string) $category->getDescription());
        } else {
            $this->view->assign('categories', $this->categoryRepository->findAll());
        }
        return $this->htmlResponse();
    }


    /**
     * Show a list of posts by given tag.
     *
     * @param Tag|null $tag
     * @param int $currentPage
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function listPostsByTagAction(?Tag $tag = null, int $currentPage = 1): ResponseInterface
    {
        if ($tag) {
            $posts = $this->postRepository->findAllByTag($tag);

            $paginationConfiguration = $this->settings['lists']['pagination'] ?? [];
            $itemsPerPage = (int)(($paginationConfiguration['itemsPerPage'] ?? '') ?: 12);
            $maximumNumberOfLinks = (int)($paginationConfiguration['maximumNumberOfLinks'] ?? 0);

            $paginator = GeneralUtility::makeInstance(QueryResultPaginator::class, $posts, $currentPage, $itemsPerPage, (int)($this->settings['limit'] ?? null), (int)($this->settings['offset'] ?? 0));
            $paginationClass = $paginationConfiguration['class'] ?? SimplePagination::class;
            $pagination = $this->getPagination2($paginationClass, $maximumNumberOfLinks, $paginator);

            $previousPageAjaxUri = '';
            if ($pagination->getPreviousPageNumber() && ($pagination->getPreviousPageNumber() >= $pagination->getFirstPageNumber())) {
                $previousPageAjaxUri = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)
                    ->setTargetPageType(74387)
                    ->setArguments([
                        'tx_blog_tag[tag]' => $tag->getUid(),
                    ])
                    ->uriFor('listPostsByTag',[
                        'currentPage' => $currentPage - 1,
                    ],
                        'Post', 'blog', 'Tag');
            }

            $nextPageAjaxUri = '';
            if ($pagination->getNextPageNumber() && ($pagination->getNextPageNumber() <= $pagination->getLastPageNumber())) {
                $nextPageAjaxUri = $this->uriBuilder->reset()->setCreateAbsoluteUri(true)
                    ->setTargetPageType(74387)
                    ->setArguments([
                        'tx_blog_tag[tag]' => $tag->getUid(),
                    ])
                    ->uriFor('listPostsByTag',[
                        'currentPage' => $currentPage + 1,
                    ],
                        'Post', 'blog', 'Tag');
            }


            $this->view->assignMultiple([
                'settings' => $this->settings,
                'nextPage' => $currentPage + 1,
                'previousPage' => $currentPage - 1,
                'pagination' => [
                    'currentPage' => $currentPage,
                    'paginator' => $paginator,
                    'pagination' => $pagination,
                ],
                'previousPageAjaxUri' => $previousPageAjaxUri,
                'nextPageAjaxUri' => $nextPageAjaxUri,
                'type' => 'bytag',
                'tag' => $tag,
            ]);

            MetaTagService::set(MetaTagService::META_TITLE, $tag->getTitle());
            MetaTagService::set(MetaTagService::META_DESCRIPTION, $tag->getDescription());
        } else {
            $this->view->assign('tags', $this->tagRepository->findAll());
        }
        return $this->htmlResponse();
    }

}
