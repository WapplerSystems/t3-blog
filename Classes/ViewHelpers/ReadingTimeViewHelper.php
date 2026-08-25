<?php

declare(strict_types=1);

namespace WapplerSystems\Blog\ViewHelpers;

use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * Computes an approximate reading time (minutes) for a blog post from the
 * word count of its colPos-0 content elements, at 200 words/minute,
 * rounded up. Returns null if the page has no body content at all, so
 * callers can omit the reading time instead of showing "0 min".
 *
 * Blog posts (doktype 137) commonly use connected-mode page translation
 * (l10n_parent), where tt_content rows for all languages sit under the SAME
 * pid and are told apart only by sys_language_uid - and where Extbase's
 * default overlay behaviour keeps Post::uid pinned to the default-language
 * page uid regardless of which translation is being rendered. The current
 * frontend language is therefore read from the Context aspect rather than
 * derived from the post.
 */
class ReadingTimeViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('pageUid', 'int', 'Page uid of the blog post', true);
        $this->registerArgument('wordsPerMinute', 'int', 'Reading speed', false, 200);
    }

    public function render(): ?int
    {
        $pageUid = (int)$this->arguments['pageUid'];
        $wordsPerMinute = (int)$this->arguments['wordsPerMinute'];
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
            ->executeQuery()
            ->fetchFirstColumn();

        $text = trim(strip_tags(implode(' ', $rows)));
        if ($text === '') {
            return null;
        }

        $wordCount = str_word_count($text, 0, 'äöüÄÖÜßàáâãéèêëíìîïóòôõúùûüñç0123456789');

        return (int)max(1, ceil($wordCount / $wordsPerMinute));
    }
}
