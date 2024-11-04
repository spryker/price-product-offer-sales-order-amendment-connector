<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\ConfigurableBundleCart;

use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\ConfigurableBundleTemplateBuilder;
use Generated\Shared\DataBuilder\ConfiguredBundleBuilder;
use Generated\Shared\DataBuilder\ProductConcreteBuilder;
use Generated\Shared\DataBuilder\QuoteBuilder;
use Generated\Shared\Transfer\ConfigurableBundleTemplateSlotTransfer;
use Generated\Shared\Transfer\ConfiguredBundleItemTransfer;
use Generated\Shared\Transfer\ConfiguredBundleTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\QuoteResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\UpdateConfiguredBundleRequestTransfer;
use Spryker\Client\ConfigurableBundleCart\Dependency\Client\ConfigurableBundleCartToCartClientBridge;
use Spryker\Client\ConfigurableBundleCart\Reader\QuoteItemReader;
use Spryker\Client\ConfigurableBundleCart\Updater\QuoteItemUpdater;
use Spryker\Client\ConfigurableBundleCart\Writer\CartWriter;
use Spryker\Shared\Kernel\Transfer\Exception\RequiredTransferPropertyException;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group ConfigurableBundleCart
 * @group ConfigurableBundleCartWriterTest
 * Add your own group annotations below this line
 */
class ConfigurableBundleCartWriterTest extends Unit
{
    /**
     * @var string
     */
    protected const FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_1 = 'FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_1';

    /**
     * @var string
     */
    protected const FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_2 = 'FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_2';

    /**
     * @var string
     */
    protected const FAKE_CONFIGURABLE_BUNDLE_UUID_1 = 'FAKE_CONFIGURABLE_BUNDLE_UUID_1';

    /**
     * @var string
     */
    protected const FAKE_CONFIGURABLE_BUNDLE_UUID_2 = 'FAKE_CONFIGURABLE_BUNDLE_UUID_2';

    /**
     * @var string
     */
    protected const FAKE_CONFIGURABLE_BUNDLE_SLOT_UUID_1 = 'FAKE_CONFIGURABLE_BUNDLE_SLOT_UUID_1';

    /**
     * @var string
     */
    protected const FAKE_CONFIGURABLE_BUNDLE_SLOT_UUID_2 = 'FAKE_CONFIGURABLE_BUNDLE_SLOT_UUID_2';

    /**
     * @var string
     */
    protected const FAKE_CONFIGURABLE_BUNDLE_SLOT_UUID_3 = 'FAKE_CONFIGURABLE_BUNDLE_SLOT_UUID_3';

    /**
     * @var string
     */
    protected const FAKE_CONFIGURABLE_BUNDLE_SLOT_UUID_4 = 'FAKE_CONFIGURABLE_BUNDLE_SLOT_UUID_4';

    /**
     * @var string
     */
    protected const FAKE_CONFIGURABLE_BUNDLE_SLOT_UUID_5 = 'FAKE_CONFIGURABLE_BUNDLE_SLOT_UUID_5';

    /**
     * @see \Spryker\Client\ConfigurableBundleCart\Writer\CartWriter::GLOSSARY_KEY_CONFIGURED_BUNDLE_NOT_FOUND
     *
     * @var string
     */
    protected const GLOSSARY_KEY_CONFIGURED_BUNDLE_NOT_FOUND = 'configured_bundle_cart.error.configured_bundle_not_found';

    /**
     * @see \Spryker\Client\ConfigurableBundleCart\Writer\CartWriter::GLOSSARY_KEY_CONFIGURED_BUNDLE_CANNOT_BE_REMOVED
     *
     * @var string
     */
    protected const GLOSSARY_KEY_CONFIGURED_BUNDLE_CANNOT_BE_REMOVED = 'configured_bundle_cart.error.configured_bundle_cannot_be_removed';

    /**
     * @see \Spryker\Client\ConfigurableBundleCart\Writer\CartWriter::GLOSSARY_KEY_CONFIGURED_BUNDLE_CANNOT_BE_UPDATED
     *
     * @var string
     */
    protected const GLOSSARY_KEY_CONFIGURED_BUNDLE_CANNOT_BE_UPDATED = 'configured_bundle_cart.error.configured_bundle_cannot_be_updated';

    /**
     * @return void
     */
    public function testRemoveConfiguredBundleRemovesConfiguredBundleFromCart(): void
    {
        // Arrange
        $quoteTransfer = $this->getFakeQuoteWithConfiguredBundleItems();

        $updateConfiguredBundleRequestTransfer = (new UpdateConfiguredBundleRequestTransfer())
            ->setGroupKey(static::FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_1)
            ->setQuote($quoteTransfer);

        $cartClientMock = $this->createCartClientMock();

        $cartClientMock
            ->method('removeFromCart')
            ->willReturn((new QuoteResponseTransfer())->setIsSuccessful(true));

        $cartWriterMock = $this->createCartWriterMock($cartClientMock);

        // Act
        $quoteResponseTransfer = $cartWriterMock->removeConfiguredBundle($updateConfiguredBundleRequestTransfer);

        // Assert
        $this->assertTrue($quoteResponseTransfer->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testRemoveConfiguredBundleThrowsExceptionWithoutGroupKey(): void
    {
        // Arrange
        $quoteTransfer = $this->getFakeQuoteWithConfiguredBundleItems();

        $updateConfiguredBundleRequestTransfer = (new UpdateConfiguredBundleRequestTransfer())
            ->setQuote($quoteTransfer);

        $cartWriterMock = $this->createCartWriterMock($this->createCartClientMock());

        // Assert
        $this->expectException(RequiredTransferPropertyException::class);

        // Act
        $cartWriterMock->removeConfiguredBundle($updateConfiguredBundleRequestTransfer);
    }

    /**
     * @return void
     */
    public function testRemoveConfiguredBundleThrowsExceptionWithoutQuote(): void
    {
        // Arrange
        $updateConfiguredBundleRequestTransfer = (new UpdateConfiguredBundleRequestTransfer())
            ->setGroupKey(static::FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_1);

        $cartWriterMock = $this->createCartWriterMock($this->createCartClientMock());

        // Assert
        $this->expectException(RequiredTransferPropertyException::class);

        // Act
        $cartWriterMock->removeConfiguredBundle($updateConfiguredBundleRequestTransfer);
    }

    /**
     * @return void
     */
    public function testRemoveConfiguredBundleThrowsErrorMessageWithWrongConfiguredBundleGroupKey(): void
    {
        // Arrange
        $quoteTransfer = $this->getFakeQuoteWithConfiguredBundleItems();

        $updateConfiguredBundleRequestTransfer = (new UpdateConfiguredBundleRequestTransfer())
            ->setGroupKey('FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_3')
            ->setQuote($quoteTransfer);

        $cartWriterMock = $this->createCartWriterMock($this->createCartClientMock());

        // Act
        $quoteResponseTransfer = $cartWriterMock->removeConfiguredBundle($updateConfiguredBundleRequestTransfer);

        // Assert
        $this->assertFalse($quoteResponseTransfer->getIsSuccessful());
        $this->assertEquals(
            static::GLOSSARY_KEY_CONFIGURED_BUNDLE_NOT_FOUND,
            $quoteResponseTransfer->getErrors()[0]->getMessage(),
        );
    }

    /**
     * @return void
     */
    public function testRemoveConfiguredBundleThrowsErrorMessageWhenCartClientReturnError(): void
    {
        // Arrange
        $quoteTransfer = $this->getFakeQuoteWithConfiguredBundleItems();

        $updateConfiguredBundleRequestTransfer = (new UpdateConfiguredBundleRequestTransfer())
            ->setGroupKey(static::FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_1)
            ->setQuote($quoteTransfer);

        $cartClientMock = $this->createCartClientMock();

        $cartClientMock
            ->method('removeFromCart')
            ->willReturn((new QuoteResponseTransfer())->setIsSuccessful(false));

        $cartWriterMock = $this->createCartWriterMock($cartClientMock);

        // Act
        $quoteResponseTransfer = $cartWriterMock->removeConfiguredBundle($updateConfiguredBundleRequestTransfer);

        // Assert
        $this->assertFalse($quoteResponseTransfer->getIsSuccessful());
        $this->assertEquals(
            static::GLOSSARY_KEY_CONFIGURED_BUNDLE_CANNOT_BE_REMOVED,
            $quoteResponseTransfer->getErrors()[0]->getMessage(),
        );
    }

    /**
     * @return void
     */
    public function testUpdateConfiguredBundleQuantityChangesConfiguredBundleQuantity(): void
    {
        // Arrange
        $quoteTransfer = $this->getFakeQuoteWithConfiguredBundleItems();

        $updateConfiguredBundleRequestTransfer = (new UpdateConfiguredBundleRequestTransfer())
            ->setGroupKey(static::FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_1)
            ->setQuantity(5)
            ->setQuote($quoteTransfer);

        $cartClientMock = $this->createCartClientMock();

        $cartClientMock
            ->method('updateQuantity')
            ->willReturn((new QuoteResponseTransfer())->setIsSuccessful(true));

        $cartWriterMock = $this->createCartWriterMock($cartClientMock);

        // Act
        $quoteResponseTransfer = $cartWriterMock->updateConfiguredBundleQuantity($updateConfiguredBundleRequestTransfer);

        // Assert
        $this->assertTrue($quoteResponseTransfer->getIsSuccessful());
    }

    /**
     * @return void
     */
    public function testUpdateConfiguredBundleThrowsExceptionWithoutQuantity(): void
    {
        // Arrange
        $quoteTransfer = $this->getFakeQuoteWithConfiguredBundleItems();

        $updateConfiguredBundleRequestTransfer = (new UpdateConfiguredBundleRequestTransfer())
            ->setQuote($quoteTransfer)
            ->setGroupKey(static::FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_1);

        $cartWriterMock = $this->createCartWriterMock($this->createCartClientMock());

        // Assert
        $this->expectException(RequiredTransferPropertyException::class);

        // Act
        $cartWriterMock->updateConfiguredBundleQuantity($updateConfiguredBundleRequestTransfer);
    }

    /**
     * @return void
     */
    public function testUpdateConfiguredBundleThrowsExceptionWithoutGroupKey(): void
    {
        // Arrange
        $quoteTransfer = $this->getFakeQuoteWithConfiguredBundleItems();

        $updateConfiguredBundleRequestTransfer = (new UpdateConfiguredBundleRequestTransfer())
            ->setQuote($quoteTransfer)
            ->setQuantity(5);

        $cartWriterMock = $this->createCartWriterMock($this->createCartClientMock());

        // Assert
        $this->expectException(RequiredTransferPropertyException::class);

        // Act
        $cartWriterMock->updateConfiguredBundleQuantity($updateConfiguredBundleRequestTransfer);
    }

    /**
     * @return void
     */
    public function testUpdateConfiguredBundleThrowsExceptionWithoutQuote(): void
    {
        // Arrange
        $updateConfiguredBundleRequestTransfer = (new UpdateConfiguredBundleRequestTransfer())
            ->setGroupKey(static::FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_1)
            ->setQuantity(5);

        $cartWriterMock = $this->createCartWriterMock($this->createCartClientMock());

        // Assert
        $this->expectException(RequiredTransferPropertyException::class);

        // Act
        $cartWriterMock->updateConfiguredBundleQuantity($updateConfiguredBundleRequestTransfer);
    }

    /**
     * @return void
     */
    public function testUpdateConfiguredBundleThrowsErrorMessageWithWrongConfiguredBundleGroupKey(): void
    {
        // Arrange
        $quoteTransfer = $this->getFakeQuoteWithConfiguredBundleItems();

        $updateConfiguredBundleRequestTransfer = (new UpdateConfiguredBundleRequestTransfer())
            ->setGroupKey('FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_3')
            ->setQuantity(5)
            ->setQuote($quoteTransfer);

        $cartWriterMock = $this->createCartWriterMock($this->createCartClientMock());

        // Act
        $quoteResponseTransfer = $cartWriterMock->updateConfiguredBundleQuantity($updateConfiguredBundleRequestTransfer);

        // Assert
        $this->assertFalse($quoteResponseTransfer->getIsSuccessful());
        $this->assertEquals(
            static::GLOSSARY_KEY_CONFIGURED_BUNDLE_NOT_FOUND,
            $quoteResponseTransfer->getErrors()[0]->getMessage(),
        );
    }

    /**
     * @return void
     */
    public function testUpdateConfiguredBundleThrowsErrorMessageWhenCartClientReturnError(): void
    {
        // Arrange
        $quoteTransfer = $this->getFakeQuoteWithConfiguredBundleItems();

        $updateConfiguredBundleRequestTransfer = (new UpdateConfiguredBundleRequestTransfer())
            ->setGroupKey(static::FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_1)
            ->setQuantity(5)
            ->setQuote($quoteTransfer);

        $cartClientMock = $this->createCartClientMock();

        $cartClientMock
            ->method('updateQuantity')
            ->willReturn((new QuoteResponseTransfer())->setIsSuccessful(false));

        $cartWriterMock = $this->createCartWriterMock($cartClientMock);

        // Act
        $quoteResponseTransfer = $cartWriterMock->updateConfiguredBundleQuantity($updateConfiguredBundleRequestTransfer);

        // Assert
        $this->assertFalse($quoteResponseTransfer->getIsSuccessful());
        $this->assertEquals(
            static::GLOSSARY_KEY_CONFIGURED_BUNDLE_CANNOT_BE_UPDATED,
            $quoteResponseTransfer->getErrors()[0]->getMessage(),
        );
    }

    /**
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function getFakeQuoteWithConfiguredBundleItems(): QuoteTransfer
    {
        return (new QuoteBuilder())
            ->withItem([
                ItemTransfer::SKU => (new ProductConcreteBuilder())->build()->getSku(),
                ItemTransfer::UNIT_PRICE => 1,
                ItemTransfer::QUANTITY => 1,
                ItemTransfer::CONFIGURED_BUNDLE_ITEM => $this->createConfiguredBundleItem(static::FAKE_CONFIGURABLE_BUNDLE_SLOT_UUID_1),
                ItemTransfer::CONFIGURED_BUNDLE => $this->createConfiguredBundle(
                    static::FAKE_CONFIGURABLE_BUNDLE_UUID_1,
                    static::FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_1,
                ),
            ])
            ->withItem([
                ItemTransfer::SKU => (new ProductConcreteBuilder())->build()->getSku(),
                ItemTransfer::UNIT_PRICE => 1,
                ItemTransfer::QUANTITY => 1,
                ItemTransfer::CONFIGURED_BUNDLE_ITEM => $this->createConfiguredBundleItem(static::FAKE_CONFIGURABLE_BUNDLE_SLOT_UUID_2),
                ItemTransfer::CONFIGURED_BUNDLE => $this->createConfiguredBundle(
                    static::FAKE_CONFIGURABLE_BUNDLE_UUID_1,
                    static::FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_1,
                ),
            ])
            ->withItem([
                ItemTransfer::SKU => (new ProductConcreteBuilder())->build()->getSku(),
                ItemTransfer::UNIT_PRICE => 1,
                ItemTransfer::QUANTITY => 1,
                ItemTransfer::CONFIGURED_BUNDLE_ITEM => $this->createConfiguredBundleItem(static::FAKE_CONFIGURABLE_BUNDLE_SLOT_UUID_3),
                ItemTransfer::CONFIGURED_BUNDLE => $this->createConfiguredBundle(
                    static::FAKE_CONFIGURABLE_BUNDLE_UUID_2,
                    static::FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_2,
                ),
            ])
            ->withItem([
                ItemTransfer::SKU => (new ProductConcreteBuilder())->build()->getSku(),
                ItemTransfer::UNIT_PRICE => 1,
                ItemTransfer::QUANTITY => 1,
                ItemTransfer::CONFIGURED_BUNDLE_ITEM => $this->createConfiguredBundleItem(static::FAKE_CONFIGURABLE_BUNDLE_SLOT_UUID_4),
                ItemTransfer::CONFIGURED_BUNDLE => $this->createConfiguredBundle(
                    static::FAKE_CONFIGURABLE_BUNDLE_UUID_2,
                    static::FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_2,
                ),
            ])
            ->withItem([
                ItemTransfer::SKU => (new ProductConcreteBuilder())->build()->getSku(),
                ItemTransfer::UNIT_PRICE => 1,
                ItemTransfer::QUANTITY => 1,
                ItemTransfer::CONFIGURED_BUNDLE_ITEM => $this->createConfiguredBundleItem(static::FAKE_CONFIGURABLE_BUNDLE_SLOT_UUID_5),
                ItemTransfer::CONFIGURED_BUNDLE => $this->createConfiguredBundle(
                    static::FAKE_CONFIGURABLE_BUNDLE_UUID_2,
                    static::FAKE_CONFIGURABLE_BUNDLE_GROUP_KEY_2,
                ),
            ])
            ->build();
    }

    /**
     * @param string|null $templateUuid
     * @param string|null $groupKey
     *
     * @return \Generated\Shared\Transfer\ConfiguredBundleTransfer
     */
    protected function createConfiguredBundle(?string $templateUuid = null, ?string $groupKey = null): ConfiguredBundleTransfer
    {
        return (new ConfiguredBundleBuilder())->build()
            ->setTemplate((new ConfigurableBundleTemplateBuilder())->build()->setUuid($templateUuid))
            ->setGroupKey($groupKey);
    }

    /**
     * @param string|null $slotUuid
     *
     * @return \Generated\Shared\Transfer\ConfiguredBundleItemTransfer
     */
    protected function createConfiguredBundleItem(?string $slotUuid = null): ConfiguredBundleItemTransfer
    {
        return (new ConfiguredBundleItemTransfer())
            ->setSlot((new ConfigurableBundleTemplateSlotTransfer())->setUuid($slotUuid))
            ->setQuantityPerSlot(1);
    }

    /**
     * @param \PHPUnit\Framework\MockObject\MockObject|\Spryker\Client\ConfigurableBundleCart\Dependency\Client\ConfigurableBundleCartToCartClientBridge $cartClientMock
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Client\ConfigurableBundleCart\Writer\CartWriter
     */
    protected function createCartWriterMock($cartClientMock): CartWriter
    {
        return $this->getMockBuilder(CartWriter::class)
            ->setConstructorArgs([
                $cartClientMock,
                $this->createQuoteItemReaderMock(),
                $this->createQuoteItemUpdaterMock(),
            ])
            ->onlyMethods([])
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Client\ConfigurableBundleCart\Dependency\Client\ConfigurableBundleCartToCartClientBridge
     */
    protected function createCartClientMock(): ConfigurableBundleCartToCartClientBridge
    {
        return $this->getMockBuilder(ConfigurableBundleCartToCartClientBridge::class)
            ->onlyMethods([
                'removeFromCart',
                'updateQuantity',
            ])
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Client\ConfigurableBundleCart\Updater\QuoteItemUpdater
     */
    protected function createQuoteItemUpdaterMock(): QuoteItemUpdater
    {
        return $this->getMockBuilder(QuoteItemUpdater::class)
            ->setConstructorArgs([
                $this->createQuoteItemReaderMock(),
            ])
            ->onlyMethods([])
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Client\ConfigurableBundleCart\Reader\QuoteItemReader
     */
    protected function createQuoteItemReaderMock(): QuoteItemReader
    {
        return $this->getMockBuilder(QuoteItemReader::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
    }
}
