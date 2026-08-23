<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\ViewHelpers\Schema;

use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use WapplerSystems\Blog\Domain\Model\Author;
use WapplerSystems\Blog\Utility\SchemaUtility;

class PersonViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('author', Author::class, 'The author', true);
    }

    public function render(): string
    {
        /** @var Author $author */
        $author = $this->arguments['author'];
        $authorData = SchemaUtility::buildPersonData($author);
        if ($author->getDetailsPage() > 0) {
            $authorData['sameAs'][] = GeneralUtility::makeInstance(UriBuilder::class)->reset()
                ->setRequest($this->getRequest())
                ->setTargetPageUid($author->getDetailsPage())
                ->setCreateAbsoluteUri(true)
                ->build();
        }

        return (string)json_encode($authorData, SchemaUtility::JSON_ENCODE_OPTIONS);
    }

    protected function getRequest(): RequestInterface
    {
        if (null === $this->renderingContext) {
            throw new \RuntimeException('ViewHelper blogvh:schema.person requires an existing rendering context.', 1784623878);
        }
        $request = null;
        if ($this->renderingContext->hasAttribute(ServerRequestInterface::class)) {
            $request = $this->renderingContext->getAttribute(ServerRequestInterface::class);
        }

        if (!$request instanceof RequestInterface) {
            throw new \RuntimeException(
                'ViewHelper blogvh:schema.person can be used only in extbase context and needs a request implementing extbase RequestInterface.',
                1784623879
            );
        }

        return $request;
    }
}
