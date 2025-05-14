<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\SspServiceManagement\Business\Saver;

use Generated\Shared\Transfer\QuoteTransfer;

interface ServiceDateTimeEnabledSaverInterface
{
    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return void
     */
    public function saveServiceDateTimeEnabledForOrderItems(QuoteTransfer $quoteTransfer): void;
}
