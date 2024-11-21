<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\Customer;

use Spryker\Shared\Kernel\AbstractSharedConfig;

class CustomerConfig extends AbstractSharedConfig
{
    /**
     * @var string
     */
    public const ANONYMOUS_SESSION_KEY = 'anonymousID';

    /**
     * @api
     *
     * @return bool
     */
    public function isDoubleOptInEnabled(): bool
    {
        return false;
    }
}
