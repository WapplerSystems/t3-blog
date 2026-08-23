<?php
declare(strict_types = 1);

/*
 * This file is part of the package wapplersystems/blog.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace WapplerSystems\Blog\Updates\Criteria;

use Doctrine\DBAL\ArrayParameterType;

class InCriteria extends AbstractCriteria implements CriteriaInterface
{
    protected array $values;

    public function setValues(array $values): self
    {
        $this->values = $values;
        return $this;
    }

    public function getValues(): array
    {
        return $this->values;
    }

    public function __toString(): string
    {
        return $this->queryBuilder->expr()->in(
            $this->getField(),
            $this->queryBuilder->createNamedParameter(
                $this->getValues(),
                ArrayParameterType::STRING
            )
        );
    }
}
