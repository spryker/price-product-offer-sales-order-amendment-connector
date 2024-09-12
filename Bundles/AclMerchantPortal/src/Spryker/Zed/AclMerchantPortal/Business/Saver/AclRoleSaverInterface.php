<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AclMerchantPortal\Business\Saver;

use Generated\Shared\Transfer\MerchantTransfer;
use Generated\Shared\Transfer\MerchantUserTransfer;
use Generated\Shared\Transfer\RoleTransfer;

interface AclRoleSaverInterface
{
    /**
     * @param \Generated\Shared\Transfer\MerchantTransfer $merchantTransfer
     *
     * @return \Generated\Shared\Transfer\RoleTransfer
     */
    public function saveMerchantAclRole(MerchantTransfer $merchantTransfer): RoleTransfer;

    /**
     * @param \Generated\Shared\Transfer\MerchantUserTransfer $merchantUserTransfer
     *
     * @return \Generated\Shared\Transfer\RoleTransfer
     */
    public function saveMerchantUserAclRole(MerchantUserTransfer $merchantUserTransfer): RoleTransfer;
}
