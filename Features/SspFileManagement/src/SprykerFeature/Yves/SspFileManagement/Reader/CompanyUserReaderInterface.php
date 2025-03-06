<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\SspFileManagement\Reader;

use Generated\Shared\Transfer\CompanyUserTransfer;

interface CompanyUserReaderInterface
{
    /**
     * @return \Generated\Shared\Transfer\CompanyUserTransfer
     */
    public function getCurrentCompanyUser(): CompanyUserTransfer;
}
