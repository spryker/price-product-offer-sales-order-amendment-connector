<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Customer\Persistence\Propel;

use Orm\Zed\Customer\Persistence\Base\SpyCustomerQuery as BaseSpyCustomerQuery;
use Orm\Zed\Customer\Persistence\Map\SpyCustomerTableMap;
use Propel\Runtime\ActiveQuery\Criteria;

/**
 * Skeleton subclass for performing query and update operations on the 'spy_customer' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements. This class will only be generated as
 * long as it does not already exist in the output directory.
 */
abstract class AbstractSpyCustomerQuery extends BaseSpyCustomerQuery
{
    /**
     * @param string|null $modelAlias
     * @param \Propel\Runtime\ActiveQuery\Criteria|null $criteria
     * @param bool $withAnonymized
     *
     * @return \Orm\Zed\Customer\Persistence\SpyCustomerQuery
     */
    public static function create($modelAlias = null, ?Criteria $criteria = null, $withAnonymized = false): Criteria
    {
        $query = parent::create($modelAlias, $criteria);

        if (!$withAnonymized) {
            $query->filterByAnonymizedAt(null);
        }

        return $query;
    }

    /**
     * @param list<string>|string|null $email
     * @param string $comparison
     * @param bool $ignoreCase
     *
     * @return self
     */
    public function filterByEmail($email = null, $comparison = Criteria::EQUAL, bool $ignoreCase = true): self
    {
        $query = parent::filterByEmail($email, $comparison);

        if ($ignoreCase === false) {
            /** @var \Propel\Runtime\ActiveQuery\Criterion\BasicCriterion $criterion */
            $criterion = $query->getCriterion(SpyCustomerTableMap::COL_EMAIL);
            $criterion->setIgnoreCase(false);
        }

        return $query;
    }
}
