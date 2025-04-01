<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\SspServiceManagement\Business\Reader;

use Generated\Shared\Transfer\ShipmentTypeTransfer;

interface ShipmentTypeReaderInterface
{
    /**
     * @param string $storeName
     *
     * @return \Generated\Shared\Transfer\ShipmentTypeTransfer|null
     */
    public function getDefaultShipmentType(string $storeName): ?ShipmentTypeTransfer;

    /**
     * @param array<string> $shipmentTypeUuids
     * @param string $storeName
     *
     * @return array<string, \Generated\Shared\Transfer\ShipmentTypeTransfer>
     */
    public function getShipmentTypesIndexedByUuids(array $shipmentTypeUuids, string $storeName): array;
}
