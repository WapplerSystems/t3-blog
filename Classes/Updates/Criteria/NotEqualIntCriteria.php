<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Updates\Criteria;

use TYPO3\CMS\Core\Database\Connection;

class NotEqualIntCriteria extends AbstractCriteria implements CriteriaInterface
{
    protected int $value;

    public function setValue(int $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->queryBuilder->expr()->neq(
            $this->getField(),
            $this->queryBuilder->createNamedParameter(
                $this->getValue(),
                Connection::PARAM_INT
            )
        );
    }
}
