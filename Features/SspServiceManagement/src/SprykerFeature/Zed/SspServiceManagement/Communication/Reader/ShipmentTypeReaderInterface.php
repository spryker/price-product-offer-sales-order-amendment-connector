<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\SspServiceManagement\Communication\Reader;

use Generated\Shared\Transfer\ShipmentTypeTransfer;

interface ShipmentTypeReaderInterface
{
    /**
     * @return \Generated\Shared\Transfer\ShipmentTypeTransfer|null
     */
    public function findDefaultShipmentType(): ?ShipmentTypeTransfer;
}
