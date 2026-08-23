<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use WapplerSystems\Blog\Domain\Model\Author;

/**
 * @extends Repository<Author>
 */
class AuthorRepository extends Repository
{
    public function initializeObject(): void
    {
        $this->defaultOrderings = [
            'name' => QueryInterface::ORDER_ASCENDING,
        ];
    }
}
