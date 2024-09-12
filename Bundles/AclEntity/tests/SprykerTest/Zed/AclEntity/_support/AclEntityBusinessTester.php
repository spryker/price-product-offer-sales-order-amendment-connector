<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\AclEntity;

use Codeception\Actor;
use Orm\Zed\Acl\Persistence\SpyAclRoleQuery;
use Orm\Zed\AclEntity\Persistence\SpyAclEntityRuleQuery;
use Orm\Zed\AclEntity\Persistence\SpyAclEntitySegmentQuery;
use Orm\Zed\Merchant\Persistence\SpyAclEntitySegmentMerchantQuery;
use Propel\Runtime\Collection\ObjectCollection;

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 *
 * @method \Spryker\Zed\AclEntity\Business\AclEntityFacadeInterface getFacade(?string $moduleName = null)
 */
class AclEntityBusinessTester extends Actor
{
    use _generated\AclEntityBusinessTesterActions;

    /**
     * @return void
     */
    public function ensureAclEntityRuleDatabaseIsEmpty(): void
    {
        $this->ensureDatabaseTableIsEmpty($this->getAclEntityRuleQuery());
    }

    /**
     * @return void
     */
    public function ensureAclRoleDatabaseIsEmpty(): void
    {
        $this->ensureDatabaseTableIsEmpty($this->getAclRoleQuery());
    }

    /**
     * @param int $idAclRole
     *
     * @return int
     */
    public function getAclRulesCount(int $idAclRole): int
    {
        return $this->getAclEntityRuleQuery()->findByFkAclRole($idAclRole)->count();
    }

    /**
     * @param int $idEntitySegment
     *
     * @return \Propel\Runtime\Collection\ObjectCollection<\Orm\Zed\Merchant\Persistence\SpyAclEntitySegmentMerchant>
     */
    public function findAclEntitySegmentMerchants(int $idEntitySegment): ObjectCollection
    {
        return $this->getAclEntitySegmentMerchantQuery()->findByFkAclEntitySegment($idEntitySegment);
    }

    /**
     * @return void
     */
    public function ensureAclEntitySegmentDatabaseIsEmpty(): void
    {
        $this->ensureDatabaseTableIsEmpty($this->getAclEntitySegmentQuery());
    }

    /**
     * @return \Orm\Zed\AclEntity\Persistence\SpyAclEntityRuleQuery
     */
    protected function getAclEntityRuleQuery(): SpyAclEntityRuleQuery
    {
        return SpyAclEntityRuleQuery::create();
    }

    /**
     * @return \Orm\Zed\Merchant\Persistence\SpyAclEntitySegmentMerchantQuery
     */
    protected function getAclEntitySegmentMerchantQuery(): SpyAclEntitySegmentMerchantQuery
    {
        return SpyAclEntitySegmentMerchantQuery::create();
    }

    /**
     * @return \Orm\Zed\AclEntity\Persistence\SpyAclEntitySegmentQuery
     */
    protected function getAclEntitySegmentQuery(): SpyAclEntitySegmentQuery
    {
        return SpyAclEntitySegmentQuery::create();
    }

    /**
     * @return \Orm\Zed\Acl\Persistence\SpyAclRoleQuery
     */
    protected function getAclRoleQuery(): SpyAclRoleQuery
    {
        return SpyAclRoleQuery::create();
    }
}
