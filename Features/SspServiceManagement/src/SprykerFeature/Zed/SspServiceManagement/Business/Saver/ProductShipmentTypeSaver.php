<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\SspServiceManagement\Business\Saver;

use Generated\Shared\Transfer\EventEntityTransfer;
use Generated\Shared\Transfer\ProductConcreteTransfer;
use Spryker\Zed\Event\Business\EventFacadeInterface;
use Spryker\Zed\Kernel\Persistence\EntityManager\TransactionTrait;
use Spryker\Zed\Product\Dependency\ProductEvents;
use SprykerFeature\Zed\SspServiceManagement\Persistence\SspServiceManagementEntityManagerInterface;
use SprykerFeature\Zed\SspServiceManagement\Persistence\SspServiceManagementRepositoryInterface;

class ProductShipmentTypeSaver implements ProductShipmentTypeSaverInterface
{
    use TransactionTrait;

    /**
     * @param \SprykerFeature\Zed\SspServiceManagement\Persistence\SspServiceManagementEntityManagerInterface $sspServiceManagementEntityManager
     * @param \SprykerFeature\Zed\SspServiceManagement\Persistence\SspServiceManagementRepositoryInterface $sspServiceManagementRepository
     * @param \Spryker\Zed\Event\Business\EventFacadeInterface $eventFacade
     */
    public function __construct(
        protected SspServiceManagementEntityManagerInterface $sspServiceManagementEntityManager,
        protected SspServiceManagementRepositoryInterface $sspServiceManagementRepository,
        protected EventFacadeInterface $eventFacade
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    public function saveProductShipmentTypes(ProductConcreteTransfer $productConcreteTransfer): ProductConcreteTransfer
    {
        if (!$productConcreteTransfer->getIdProductConcrete()) {
            return $productConcreteTransfer;
        }

        return $this->getTransactionHandler()->handleTransaction(function () use ($productConcreteTransfer) {
            return $this->executeSaveProductShipmentTypesTransaction($productConcreteTransfer);
        });
    }

    /**
     * @param \Generated\Shared\Transfer\ProductConcreteTransfer $productConcreteTransfer
     *
     * @return \Generated\Shared\Transfer\ProductConcreteTransfer
     */
    protected function executeSaveProductShipmentTypesTransaction(
        ProductConcreteTransfer $productConcreteTransfer
    ): ProductConcreteTransfer {
        $idProductConcrete = $productConcreteTransfer->getIdProductConcreteOrFail();
        $existingShipmentTypeIds = $this->sspServiceManagementRepository->getShipmentTypeIdsGroupedByIdProductConcrete([$idProductConcrete])[$idProductConcrete] ?? [];
        $newShipmentTypeIds = $this->extractShipmentTypeIds($productConcreteTransfer->getShipmentTypes()->getArrayCopy());

        $shipmentTypeIdsToCreate = array_diff($newShipmentTypeIds, $existingShipmentTypeIds);
        $shipmentTypeIdsToDelete = array_diff($existingShipmentTypeIds, $newShipmentTypeIds);

        if ($shipmentTypeIdsToDelete !== []) {
            $this->sspServiceManagementEntityManager
                ->deleteProductShipmentTypesByIdProductConcreteAndShipmentTypeIds(
                    $idProductConcrete,
                    $shipmentTypeIdsToDelete,
                );
        }

        foreach ($shipmentTypeIdsToCreate as $idShipmentType) {
            $this->sspServiceManagementEntityManager->createProductShipmentType(
                $idProductConcrete,
                $idShipmentType,
            );
        }

        if ($shipmentTypeIdsToCreate !== [] || $shipmentTypeIdsToDelete !== []) {
            $this->triggerProductUpdateEvent($idProductConcrete);
        }

        return $productConcreteTransfer;
    }

    /**
     * @param list<\Generated\Shared\Transfer\ShipmentTypeTransfer> $shipmentTypeTransfers
     *
     * @return list<int>
     */
    protected function extractShipmentTypeIds(array $shipmentTypeTransfers): array
    {
        $shipmentTypeIds = [];
        foreach ($shipmentTypeTransfers as $shipmentTypeTransfer) {
            $shipmentTypeIds[] = $shipmentTypeTransfer->getIdShipmentTypeOrFail();
        }

        return $shipmentTypeIds;
    }

    /**
     * @param int $idProductConcrete
     *
     * @return void
     */
    protected function triggerProductUpdateEvent(int $idProductConcrete): void
    {
        $eventEntityTransfer = (new EventEntityTransfer())->setId($idProductConcrete);

        $this->eventFacade->triggerBulk(ProductEvents::PRODUCT_CONCRETE_PUBLISH, [$eventEntityTransfer]);
    }
}
