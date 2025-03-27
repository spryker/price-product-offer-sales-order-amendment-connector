<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\SspAssetManagement\Permission;

use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\SspAssetConditionsTransfer;
use Generated\Shared\Transfer\SspAssetCriteriaTransfer;
use Spryker\Yves\Kernel\PermissionAwareTrait;
use SprykerFeature\Shared\SspAssetManagement\Plugin\Permission\ViewBusinessUnitSspAssetPermissionPlugin;
use SprykerFeature\Shared\SspAssetManagement\Plugin\Permission\ViewCompanySspAssetPermissionPlugin;

class SspAssetCustomerPermissionExpander implements SspAssetCustomerPermissionExpanderInterface
{
    use PermissionAwareTrait;

    /**
     * @param \Generated\Shared\Transfer\SspAssetCriteriaTransfer $sspAssetCriteriaTransfer
     * @param \Generated\Shared\Transfer\CompanyUserTransfer $companyUserTransfer
     *
     * @return \Generated\Shared\Transfer\SspAssetCriteriaTransfer
     */
    public function expand(SspAssetCriteriaTransfer $sspAssetCriteriaTransfer, CompanyUserTransfer $companyUserTransfer): SspAssetCriteriaTransfer
    {
        if (!$sspAssetCriteriaTransfer->getSspAssetConditions()) {
            $sspAssetCriteriaTransfer->setSspAssetConditions(new SspAssetConditionsTransfer());
        }

        if ($this->can(ViewCompanySspAssetPermissionPlugin::KEY)) {
            $sspAssetCriteriaTransfer->getSspAssetConditionsOrFail()->setAssignedBusinessUnitCompanyId($companyUserTransfer->getFkCompanyOrFail());

            return $sspAssetCriteriaTransfer;
        }

        if ($this->can(ViewBusinessUnitSspAssetPermissionPlugin::KEY)) {
            $sspAssetCriteriaTransfer->getSspAssetConditionsOrFail()->setAssignedBusinessUnitId($companyUserTransfer->getFkCompanyBusinessUnitOrFail());
        }

        return $sspAssetCriteriaTransfer;
    }
}
