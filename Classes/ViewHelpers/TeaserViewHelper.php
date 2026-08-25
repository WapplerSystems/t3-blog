<?php

declare(strict_types=1);

namespace WapplerSystems\Blog\ViewHelpers;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Blog-list teaser text with a three-level fallback: intro -> description ->
 * first ~N characters of the post's own bodytext (word-boundary trimmed).
 * Only the third level needs a DB lookup, so intro/description (already
 * available on the post) are checked first without querying.
 *
 * See ReadingTimeViewHelper for why the bodytext lookup is keyed by
 * pageUid + the current frontend language rather than by post.uid alone.
 */
class TeaserViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('intro', 'string', 'The post intro field, if any', false, '');
        $this->registerArgument('description', 'string', 'The post description field, if any', false, '');
        $this->registerArgument('pageUid', 'int', 'Page uid of the blog post (for the bodytext fallback)', true);
        $this->registerArgument('maxCharacters', 'int', 'Crop length for the bodytext fallback', false, 180);
    }

    public function render(): string
    {
        $intro = trim(strip_tags((string)$this->arguments['intro']));
        if ($intro !== '') {
            return $intro;
        }

        $description = trim((string)$this->arguments['description']);
        if ($description !== '') {
            return $description;
        }

        return $this->cropToWordBoundary($this->getBodytextExcerpt(), (int)$this->arguments['maxCharacters']);
    }

    private function getBodytextExcerpt(): string
    {
        $pageUid = (int)$this->arguments['pageUid'];
        $languageId = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id', 0);

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll();

        $rows = $queryBuilder
            ->select('bodytext')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('pid', $queryBuilder->createNamedParameter($pageUid, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('colPos', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('sys_language_uid', $queryBuilder->createNamedParameter($languageId, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('deleted', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT)),
                $queryBuilder->expr()->eq('hidden', $queryBuilder->createNamedParameter(0, Connection::PARAM_INT))
            )
            ->orderBy('sorting')
            ->executeQuery()
            ->fetchFirstColumn();

        return trim(preg_replace('/\s+/', ' ', strip_tags(implode(' ', $rows))));
    }

    private function cropToWordBoundary(string $text, int $maxCharacters): string
    {
        if ($text === '' || mb_strlen($text) <= $maxCharacters) {
            return $text;
        }

        $cropped = mb_substr($text, 0, $maxCharacters);
        $lastSpace = mb_strrpos($cropped, ' ');
        if ($lastSpace !== false) {
            $cropped = mb_substr($cropped, 0, $lastSpace);
        }

        return rtrim($cropped, " \t\n\r\0\x0B.,;:") . '…';
    }
}
