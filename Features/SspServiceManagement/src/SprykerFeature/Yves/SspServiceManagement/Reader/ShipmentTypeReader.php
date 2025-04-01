<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\SspServiceManagement\Reader;

use Generated\Shared\Transfer\ShipmentTypeStorageCollectionTransfer;
use Generated\Shared\Transfer\ShipmentTypeStorageConditionsTransfer;
use Generated\Shared\Transfer\ShipmentTypeStorageCriteriaTransfer;
use Spryker\Client\ShipmentTypeStorage\ShipmentTypeStorageClientInterface;
use SprykerFeature\Yves\SspServiceManagement\SspServiceManagementConfig;

class ShipmentTypeReader implements ShipmentTypeReaderInterface
{
    /**
     * @param \Spryker\Client\ShipmentTypeStorage\ShipmentTypeStorageClientInterface $shipmentTypeStorageClient
     * @param \SprykerFeature\Yves\SspServiceManagement\SspServiceManagementConfig $sspServiceManagementConfig
     */
    public function __construct(
        protected ShipmentTypeStorageClientInterface $shipmentTypeStorageClient,
        protected SspServiceManagementConfig $sspServiceManagementConfig
    ) {
    }

    /**
     * @param list<string> $shipmentTypeUuids
     * @param string $storeName
     *
     * @return \Generated\Shared\Transfer\ShipmentTypeStorageCollectionTransfer
     */
    public function getShipmentTypeStorageCollection(array $shipmentTypeUuids, string $storeName): ShipmentTypeStorageCollectionTransfer
    {
        if ($shipmentTypeUuids === []) {
            return new ShipmentTypeStorageCollectionTransfer();
        }

        $shipmentTypeStorageCriteriaTransfer = (new ShipmentTypeStorageCriteriaTransfer())
            ->setShipmentTypeStorageConditions(
                (new ShipmentTypeStorageConditionsTransfer())
                    ->setUuids($shipmentTypeUuids)
                    ->setStoreName($storeName),
            );

        return $this->shipmentTypeStorageClient
            ->getShipmentTypeStorageCollection($shipmentTypeStorageCriteriaTransfer);
    }
}
