<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\SspServiceManagement\Business\Expander;

use ArrayObject;
use Generated\Shared\Transfer\CartChangeTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use SprykerFeature\Zed\SspServiceManagement\Business\Reader\ServicePointReaderInterface;

class ServicePointItemExpander implements ServicePointItemExpanderInterface
{
    /**
     * @param \SprykerFeature\Zed\SspServiceManagement\Business\Reader\ServicePointReaderInterface $servicePointReader
     */
    public function __construct(protected ServicePointReaderInterface $servicePointReader)
    {
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function expandQuoteItemsWithServicePoint(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        if (!$quoteTransfer->getItems()->count()) {
            return $quoteTransfer;
        }

        $storeName = $quoteTransfer->getStoreOrFail()->getNameOrFail();
        $servicePointUuids = $this->extractServicePointUuids($quoteTransfer->getItems());

        if (!$servicePointUuids) {
            return $quoteTransfer;
        }

        $servicePointTransfersIndexedByUuid = $this->servicePointReader->getServicePointsIndexedByUuids($servicePointUuids, $storeName);

        if (!$servicePointTransfersIndexedByUuid) {
            return $quoteTransfer;
        }

        $this->expandItemsWithServicePoints($quoteTransfer->getItems(), $servicePointTransfersIndexedByUuid);
        $this->expandItemsWithServicePoints($quoteTransfer->getBundleItems(), $servicePointTransfersIndexedByUuid);

        return $quoteTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\CartChangeTransfer $cartChangeTransfer
     *
     * @return \Generated\Shared\Transfer\CartChangeTransfer
     */
    public function expandCartItemsWithServicePoint(CartChangeTransfer $cartChangeTransfer): CartChangeTransfer
    {
        if (!$cartChangeTransfer->getItems()->count()) {
            return $cartChangeTransfer;
        }

        $storeName = $cartChangeTransfer->getQuoteOrFail()->getStoreOrFail()->getNameOrFail();
        $servicePointUuids = $this->extractServicePointUuids($cartChangeTransfer->getItems());

        if (!$servicePointUuids) {
            return $cartChangeTransfer;
        }

        $servicePointTransfersIndexedByUuid = $this->servicePointReader->getServicePointsIndexedByUuids($servicePointUuids, $storeName);

        if (!$servicePointTransfersIndexedByUuid) {
            return $cartChangeTransfer;
        }

        $this->expandItemsWithServicePoints($cartChangeTransfer->getItems(), $servicePointTransfersIndexedByUuid);
        $this->expandItemsWithServicePoints($cartChangeTransfer->getQuoteOrFail()->getBundleItems(), $servicePointTransfersIndexedByUuid);

        return $cartChangeTransfer;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ItemTransfer> $itemTransfers
     *
     * @return array<string>
     */
    protected function extractServicePointUuids(ArrayObject $itemTransfers): array
    {
        $servicePointUuids = [];

        foreach ($itemTransfers as $itemTransfer) {
            if ($itemTransfer->getServicePoint() && $itemTransfer->getServicePoint()->getUuid()) {
                $servicePointUuids[] = $itemTransfer->getServicePointOrFail()->getUuidOrFail();
            }
        }

        return array_unique($servicePointUuids);
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\ItemTransfer> $itemTransfers
     * @param array<string, \Generated\Shared\Transfer\ServicePointTransfer> $servicePointTransfersIndexedByUuid
     *
     * @return void
     */
    protected function expandItemsWithServicePoints(
        ArrayObject $itemTransfers,
        array $servicePointTransfersIndexedByUuid
    ): void {
        foreach ($itemTransfers as $itemTransfer) {
            if (!$itemTransfer->getServicePoint() || !$itemTransfer->getServicePoint()->getUuid()) {
                continue;
            }

            $servicePointUuid = $itemTransfer->getServicePointOrFail()->getUuidOrFail();
            if (!isset($servicePointTransfersIndexedByUuid[$servicePointUuid])) {
                continue;
            }

            $itemTransfer->setServicePoint($servicePointTransfersIndexedByUuid[$servicePointUuid]);
        }
    }
}
