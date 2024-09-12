<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AclMerchantPortal\Dependency\Facade;

use Generated\Shared\Transfer\MerchantUserCollectionTransfer;
use Generated\Shared\Transfer\MerchantUserCriteriaTransfer;

interface AclMerchantPortalToMerchantUserFacadeInterface
{
    /**
     * @param \Generated\Shared\Transfer\MerchantUserCriteriaTransfer $merchantUserCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\MerchantUserCollectionTransfer
     */
    public function getMerchantUserCollection(MerchantUserCriteriaTransfer $merchantUserCriteriaTransfer): MerchantUserCollectionTransfer;
}
