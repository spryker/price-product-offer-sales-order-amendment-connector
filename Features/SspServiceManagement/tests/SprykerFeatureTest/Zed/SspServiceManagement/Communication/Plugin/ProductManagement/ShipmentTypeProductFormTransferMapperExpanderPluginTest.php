<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\SspServiceManagement\Communication\Plugin\ProductManagement;

use ArrayObject;
use Codeception\Test\Unit;
use SprykerFeature\Zed\SspServiceManagement\Communication\Form\ShipmentTypeProductConcreteForm;
use SprykerFeature\Zed\SspServiceManagement\Communication\Plugin\ProductManagement\ShipmentTypeProductFormTransferMapperExpanderPlugin;
use SprykerFeatureTest\Zed\SspServiceManagement\SspServiceManagementCommunicationTester;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group SspServiceManagement
 * @group Communication
 * @group Plugin
 * @group ProductManagement
 * @group ShipmentTypeProductFormTransferMapperExpanderPluginTest
 */
class ShipmentTypeProductFormTransferMapperExpanderPluginTest extends Unit
{
    /**
     * @var \SprykerFeatureTest\Zed\SspServiceManagement\SspServiceManagementCommunicationTester
     */
    protected SspServiceManagementCommunicationTester $tester;

    /**
     * @return void
     */
    protected function _before(): void
    {
        $this->tester->ensureProductShipmentTypeTableIsEmpty();
    }

    /**
     * @return void
     */
    public function testMapShouldMapFormDataToProductConcreteTransfer(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->haveProduct();
        $shipmentTypeTransfer = $this->tester->haveShipmentType();
        $formData = [
            ShipmentTypeProductConcreteForm::FIELD_SHIPMENT_TYPES => new ArrayObject([$shipmentTypeTransfer]),
        ];

        // Act
        $productConcreteTransfer = (new ShipmentTypeProductFormTransferMapperExpanderPlugin())
            ->map($productConcreteTransfer, $formData);

        // Assert
        $this->assertCount(1, $productConcreteTransfer->getShipmentTypes());
        $this->assertSame(
            $shipmentTypeTransfer->getIdShipmentTypeOrFail(),
            $productConcreteTransfer->getShipmentTypes()[0]->getIdShipmentTypeOrFail(),
        );
    }

    /**
     * @return void
     */
    public function testMapShouldNotMapFormDataWhenShipmentTypesAreNotProvided(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->haveProduct();
        $formData = [];

        // Act
        $productConcreteTransfer = (new ShipmentTypeProductFormTransferMapperExpanderPlugin())
            ->map($productConcreteTransfer, $formData);

        // Assert
        $this->assertCount(0, $productConcreteTransfer->getShipmentTypes());
    }

    /**
     * @return void
     */
    public function testMapShouldMapMultipleShipmentTypesToProductConcreteTransfer(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->haveProduct();
        $firstShipmentTypeTransfer = $this->tester->haveShipmentType();
        $secondShipmentTypeTransfer = $this->tester->haveShipmentType();
        $formData = [
            ShipmentTypeProductConcreteForm::FIELD_SHIPMENT_TYPES => new ArrayObject([
                $firstShipmentTypeTransfer,
                $secondShipmentTypeTransfer,
            ]),
        ];

        // Act
        $productConcreteTransfer = (new ShipmentTypeProductFormTransferMapperExpanderPlugin())
            ->map($productConcreteTransfer, $formData);

        // Assert
        $this->assertCount(2, $productConcreteTransfer->getShipmentTypes());
        $this->assertSame(
            $firstShipmentTypeTransfer->getIdShipmentTypeOrFail(),
            $productConcreteTransfer->getShipmentTypes()[0]->getIdShipmentTypeOrFail(),
        );
        $this->assertSame(
            $secondShipmentTypeTransfer->getIdShipmentTypeOrFail(),
            $productConcreteTransfer->getShipmentTypes()[1]->getIdShipmentTypeOrFail(),
        );
    }

    /**
     * @return void
     */
    public function testMapShouldOverwriteExistingShipmentTypes(): void
    {
        // Arrange
        $productConcreteTransfer = $this->tester->haveProduct();
        $existingShipmentTypeTransfer = $this->tester->haveShipmentType();
        $newShipmentTypeTransfer = $this->tester->haveShipmentType();

        $productConcreteTransfer = $this->tester->addShipmentTypesToProduct(
            $productConcreteTransfer,
            [$existingShipmentTypeTransfer],
        );

        $formData = [
            ShipmentTypeProductConcreteForm::FIELD_SHIPMENT_TYPES => new ArrayObject([$newShipmentTypeTransfer]),
        ];

        // Act
        $productConcreteTransfer = (new ShipmentTypeProductFormTransferMapperExpanderPlugin())
            ->map($productConcreteTransfer, $formData);

        // Assert
        $this->assertCount(1, $productConcreteTransfer->getShipmentTypes());
        $this->assertSame(
            $newShipmentTypeTransfer->getIdShipmentTypeOrFail(),
            $productConcreteTransfer->getShipmentTypes()[0]->getIdShipmentTypeOrFail(),
        );
    }
}
