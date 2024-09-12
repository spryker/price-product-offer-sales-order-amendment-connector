<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AclMerchantPortal\Dependency\Facade;

use Generated\Shared\Transfer\MerchantUserCollectionTransfer;
use Generated\Shared\Transfer\MerchantUserCriteriaTransfer;

class AclMerchantPortalToMerchantUserFacadeBridge implements AclMerchantPortalToMerchantUserFacadeInterface
{
    /**
     * @var \Spryker\Zed\MerchantUser\Business\MerchantUserFacadeInterface
     */
    protected $merchantUserFacade;

    /**
     * @param \Spryker\Zed\MerchantUser\Business\MerchantUserFacadeInterface $merchantUserFacade
     */
    public function __construct($merchantUserFacade)
    {
        $this->merchantUserFacade = $merchantUserFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\MerchantUserCriteriaTransfer $merchantUserCriteriaTransfer
     *
     * @return \Generated\Shared\Transfer\MerchantUserCollectionTransfer
     */
    public function getMerchantUserCollection(MerchantUserCriteriaTransfer $merchantUserCriteriaTransfer): MerchantUserCollectionTransfer
    {
        return $this->merchantUserFacade->getMerchantUserCollection($merchantUserCriteriaTransfer);
    }
}
