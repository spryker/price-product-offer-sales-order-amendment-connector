<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\SspServiceManagement\Communication\Plugin\Quote;

use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\ServicePointCollectionTransfer;
use Generated\Shared\Transfer\ServicePointTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Zed\ServicePoint\Business\ServicePointFacadeInterface;
use SprykerFeature\Zed\SspServiceManagement\Communication\Plugin\Quote\ServicePointQuoteExpanderPlugin;
use SprykerFeature\Zed\SspServiceManagement\SspServiceManagementDependencyProvider;
use SprykerFeatureTest\Zed\SspServiceManagement\SspServiceManagementCommunicationTester;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group SspServiceManagement
 * @group Communication
 * @group Plugin
 * @group Quote
 * @group ServicePointQuoteExpanderPluginTest
 */
class ServicePointQuoteExpanderPluginTest extends Unit
{
    /**
     * @var string
     */
    protected const TEST_SERVICE_POINT_UUID = 'test-service-point-uuid';

    /**
     * @var string
     */
    protected const TEST_STORE_NAME = 'DE';

    /**
     * @var \SprykerFeatureTest\Zed\SspServiceManagement\SspServiceManagementCommunicationTester
     */
    protected SspServiceManagementCommunicationTester $tester;

    /**
     * @return void
     */
    public function testExpandItemsExpandsItemsWithServicePoint(): void
    {
        // Arrange
        $servicePointFacadeMock = $this->createServicePointFacadeMock();
        $servicePointFacadeMock->expects($this->once())
            ->method('getServicePointCollection')
            ->willReturn($this->tester->createServicePointCollectionTransfer());

        $this->tester->setDependency(
            SspServiceManagementDependencyProvider::FACADE_SERVICE_POINT,
            $servicePointFacadeMock,
        );

        $quoteTransfer = $this->createQuoteTransferWithServicePoint(static::TEST_SERVICE_POINT_UUID);
        $servicePointQuoteExpanderPlugin = new ServicePointQuoteExpanderPlugin();

        // Act
        $resultQuoteTransfer = $servicePointQuoteExpanderPlugin->expand($quoteTransfer);

        // Assert
        $itemTransfer = $resultQuoteTransfer->getItems()[0];
        $this->assertNotNull($itemTransfer->getServicePoint());
        $this->assertSame(static::TEST_SERVICE_POINT_UUID, $itemTransfer->getServicePointOrFail()->getUuidOrFail());
        $this->assertNotNull($itemTransfer->getServicePointOrFail()->getIdServicePoint());
        $this->assertNotNull($itemTransfer->getServicePointOrFail()->getName());
    }

    /**
     * @return void
     */
    public function testExpandItemsDoesNothingWhenNoServicePointUuidProvided(): void
    {
        // Arrange
        $servicePointFacadeMock = $this->createServicePointFacadeMock();
        $servicePointFacadeMock->expects($this->never())
            ->method('getServicePointCollection');

        $this->tester->setDependency(
            SspServiceManagementDependencyProvider::FACADE_SERVICE_POINT,
            $servicePointFacadeMock,
        );

        $quoteTransfer = $this->createQuoteTransferWithoutServicePoint();

        $servicePointQuoteExpanderPlugin = new ServicePointQuoteExpanderPlugin();

        // Act
        $resultQuoteTransfer = $servicePointQuoteExpanderPlugin->expand($quoteTransfer);

        // Assert
        $itemTransfer = $resultQuoteTransfer->getItems()[0];
        $this->assertNull($itemTransfer->getServicePoint());
    }

    /**
     * @return void
     */
    public function testExpandItemsDoesNothingWhenNoItemsProvided(): void
    {
        // Arrange
        $servicePointFacadeMock = $this->createServicePointFacadeMock();
        $servicePointFacadeMock->expects($this->never())
            ->method('getServicePointCollection');

        $this->tester->setDependency(
            SspServiceManagementDependencyProvider::FACADE_SERVICE_POINT,
            $servicePointFacadeMock,
        );

        $quoteTransfer = (new QuoteTransfer())
            ->setItems(new ArrayObject())
            ->setStore((new StoreTransfer())->setName(static::TEST_STORE_NAME));

        $servicePointQuoteExpanderPlugin = new ServicePointQuoteExpanderPlugin();

        // Act
        $resultQuoteTransfer = $servicePointQuoteExpanderPlugin->expand($quoteTransfer);

        // Assert
        $this->assertCount(0, $resultQuoteTransfer->getItems());
    }

    /**
     * @return void
     */
    public function testExpandItemsDoesNothingWhenServicePointFacadeReturnsNoResults(): void
    {
        // Arrange
        $servicePointFacadeMock = $this->createServicePointFacadeMock();
        $servicePointFacadeMock->expects($this->once())
            ->method('getServicePointCollection')
            ->willReturn(new ServicePointCollectionTransfer());

        $this->tester->setDependency(
            SspServiceManagementDependencyProvider::FACADE_SERVICE_POINT,
            $servicePointFacadeMock,
        );

        $quoteTransfer = $this->createQuoteTransferWithServicePoint(static::TEST_SERVICE_POINT_UUID);

        $servicePointQuoteExpanderPlugin = new ServicePointQuoteExpanderPlugin();

        // Act
        $resultQuoteTransfer = $servicePointQuoteExpanderPlugin->expand($quoteTransfer);

        // Assert
        $itemTransfer = $resultQuoteTransfer->getItems()[0];
        $this->assertNotNull($itemTransfer->getServicePoint());
        $this->assertSame(static::TEST_SERVICE_POINT_UUID, $itemTransfer->getServicePointOrFail()->getUuidOrFail());
        $this->assertNull($itemTransfer->getServicePointOrFail()->getIdServicePoint());
        $this->assertNull($itemTransfer->getServicePointOrFail()->getName());
    }

    /**
     * @return void
     */
    public function testExpandItemsExpandsBundleItemsWithServicePoint(): void
    {
        // Arrange
        $servicePointFacadeMock = $this->createServicePointFacadeMock();
        $servicePointFacadeMock->expects($this->once())
            ->method('getServicePointCollection')
            ->willReturn($this->tester->createServicePointCollectionTransfer());

        $this->tester->setDependency(
            SspServiceManagementDependencyProvider::FACADE_SERVICE_POINT,
            $servicePointFacadeMock,
        );

        $itemTransfers = new ArrayObject([
            (new ItemTransfer())->setServicePoint(
                (new ServicePointTransfer())->setUuid(static::TEST_SERVICE_POINT_UUID),
            ),
        ]);
        $quoteTransfer = (new QuoteTransfer())
            ->setItems($itemTransfers)
            ->setStore((new StoreTransfer())->setName(static::TEST_STORE_NAME))
            ->setBundleItems($itemTransfers);

        $servicePointQuoteExpanderPlugin = new ServicePointQuoteExpanderPlugin();

        // Act
        $resultQuoteTransfer = $servicePointQuoteExpanderPlugin->expand($quoteTransfer);

        // Assert
        $bundleItemTransfer = $resultQuoteTransfer->getBundleItems()[0];
        $this->assertNotNull($bundleItemTransfer->getServicePoint());
        $this->assertSame(static::TEST_SERVICE_POINT_UUID, $bundleItemTransfer->getServicePointOrFail()->getUuidOrFail());
        $this->assertNotNull($bundleItemTransfer->getServicePointOrFail()->getIdServicePoint());
        $this->assertNotNull($bundleItemTransfer->getServicePointOrFail()->getName());
    }

    /**
     * @param string|null $servicePointUuid
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function createQuoteTransferWithServicePoint(?string $servicePointUuid = null): QuoteTransfer
    {
        $quoteTransfer = new QuoteTransfer();
        $itemTransfer = new ItemTransfer();

        if ($servicePointUuid) {
            $servicePointTransfer = new ServicePointTransfer();
            $servicePointTransfer->setUuid($servicePointUuid);
            $itemTransfer->setServicePoint($servicePointTransfer);
        }

        $quoteTransfer->setStore((new StoreTransfer())->setName(static::TEST_STORE_NAME))
            ->setBundleItems(new ArrayObject())
            ->setItems(new ArrayObject([$itemTransfer]));

        return $quoteTransfer;
    }

    /**
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function createQuoteTransferWithoutServicePoint(): QuoteTransfer
    {
        $quoteTransfer = new QuoteTransfer();
        $itemTransfer = new ItemTransfer();

        $quoteTransfer->setItems(new ArrayObject([$itemTransfer]))
            ->setStore((new StoreTransfer())->setName(static::TEST_STORE_NAME))
            ->setBundleItems(new ArrayObject());

        return $quoteTransfer;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\ServicePoint\Business\ServicePointFacadeInterface
     */
    protected function createServicePointFacadeMock(): ServicePointFacadeInterface
    {
        return $this->getMockBuilder(ServicePointFacadeInterface::class)
            ->getMock();
    }
}
