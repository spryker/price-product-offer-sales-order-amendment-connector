<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\PriceProduct;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\CurrentProductPriceTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\PriceProductFilterTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Client\PriceProduct\PriceProductClient;
use Spryker\Client\PriceProduct\PriceProductDependencyProvider;
use Spryker\Client\PriceProductExtension\Dependency\Plugin\PriceProductPostResolvePluginInterface;
use Spryker\Client\Session\SessionClient;
use Spryker\Client\Store\StoreDependencyProvider;
use Spryker\Client\StoreExtension\Dependency\Plugin\StoreExpanderPluginInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group PriceProduct
 * @group PriceProductClientTest
 * Add your own group annotations below this line
 */
class PriceProductClientTest extends Unit
{
    /**
     * @uses \Spryker\Shared\PriceProductVolume\PriceProductVolumeConfig::VOLUME_PRICE_TYPE
     *
     * @var string
     */
    protected const VOLUME_PRICE_TYPE = 'volume_prices';

    /**
     * @uses \Spryker\Shared\PriceProductVolume\PriceProductVolumeConfig::VOLUME_PRICE_QUANTITY
     *
     * @var string
     */
    protected const VOLUME_PRICE_QUANTITY = 'quantity';

    /**
     * @uses \Spryker\Shared\PriceProductVolume\PriceProductVolumeConfig::VOLUME_PRICE_NET_PRICE
     *
     * @var string
     */
    protected const VOLUME_PRICE_NET_PRICE = 'net_price';

    /**
     * @uses \Spryker\Shared\PriceProductVolume\PriceProductVolumeConfig::VOLUME_PRICE_GROSS_PRICE
     *
     * @var string
     */
    protected const VOLUME_PRICE_GROSS_PRICE = 'gross_price';

    /**
     * @uses \Spryker\Shared\PriceProduct\PriceProductConfig::PRICE_TYPE_DEFAULT
     *
     * @var string
     */
    protected const PRICE_TYPE_DEFAULT = 'DEFAULT';

    /**
     * @var string
     */
    protected const PRICE_TYPE_ORIGINAL = 'ORIGINAL';

    /**
     * @var int
     */
    protected const NET_PRICE = 22;

    /**
     * @var int
     */
    protected const GROSS_PRICE = 33;

    /**
     * @var string
     */
    protected const CURRENCY_CODE = 'EUR';

    /**
     * @var string
     */
    protected const DEFAULT_STORE = 'DE';

    /**
     * @var \SprykerTest\Client\PriceProduct\PriceProductTester
     */
    protected $tester;

    /**
     * @return void
     */
    protected function _before(): void
    {
        $this->tester->setDependency(StoreDependencyProvider::PLUGINS_STORE_EXPANDER, [
            $this->createStoreStorageStoreExpanderPluginMock(),
        ]);
    }

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $sessionContainer = new Session(new MockArraySessionStorage());
        $sessionClient = new SessionClient();
        $sessionClient->setContainer($sessionContainer);
    }

    /**
     * @return void
     */
    public function testResolveProductPriceTransferWillReturnPriceDataByPriceType(): void
    {
        // Arrange
        $priceProductTransferDefault = $this->createPriceProductTransfer(static::NET_PRICE, static::GROSS_PRICE, static::PRICE_TYPE_DEFAULT);
        $volumePriceDataDefaultDefaultJson = json_encode([
            static::VOLUME_PRICE_TYPE => $this->getVolumePriceDataDefault(),
        ]);
        $priceProductTransferDefault->getMoneyValue()
            ->setPriceData($volumePriceDataDefaultDefaultJson);

        $priceProductTransferOrigin = $this->createPriceProductTransfer(static::NET_PRICE, static::GROSS_PRICE, static::PRICE_TYPE_ORIGINAL);
        $volumePriceDataOriginJson = json_encode([
            static::VOLUME_PRICE_TYPE => $this->getVolumePriceDataOrigin(),
        ]);
        $priceProductTransferOrigin->getMoneyValue()
            ->setPriceData($volumePriceDataOriginJson);

        // Act
        $currentProductPriceTransfer = (new PriceProductClient())->resolveProductPriceTransfer([
            $priceProductTransferDefault,
            $priceProductTransferOrigin,
        ]);

        // Assert
        $this->makeAsserts($currentProductPriceTransfer, $volumePriceDataDefaultDefaultJson, $volumePriceDataOriginJson);
    }

    /**
     * @return void
     */
    public function testResolveProductPriceTransferByPriceProductFilterWillReturnPriceDataByPriceType(): void
    {
        // Arrange
        $priceProductTransferDefault = $this->createPriceProductTransfer(static::NET_PRICE, static::GROSS_PRICE, static::PRICE_TYPE_DEFAULT);
        $volumePriceDataDefaultDefaultJson = json_encode([
            static::VOLUME_PRICE_TYPE => $this->getVolumePriceDataDefault(),
        ]);
        $priceProductTransferDefault->getMoneyValue()
            ->setPriceData($volumePriceDataDefaultDefaultJson);

        $priceProductTransferOrigin = $this->createPriceProductTransfer(static::NET_PRICE, static::GROSS_PRICE, static::PRICE_TYPE_ORIGINAL);
        $volumePriceDataOriginJson = json_encode([
            static::VOLUME_PRICE_TYPE => $this->getVolumePriceDataOrigin(),
        ]);
        $priceProductTransferOrigin->getMoneyValue()
            ->setPriceData($volumePriceDataOriginJson);

        $priceProductFilterTransfer = new PriceProductFilterTransfer();

        // Act
        $currentProductPriceTransfer = (new PriceProductClient())->resolveProductPriceTransferByPriceProductFilter([
            $priceProductTransferDefault,
            $priceProductTransferOrigin,
        ], $priceProductFilterTransfer);

        // Assert
        $this->makeAsserts($currentProductPriceTransfer, $volumePriceDataDefaultDefaultJson, $volumePriceDataOriginJson);
    }

    /**
     * @return void
     */
    public function testShouldExecutePriceProductPostResolvePluginStackInResolveProductPriceTransferByPriceProductFilter(): void
    {
        // Assert
        $this->tester->setDependency(
            PriceProductDependencyProvider::PLUGINS_PRICE_PRODUCT_POST_RESOLVE,
            [
                $this->getPriceProductPostResolvePluginMock(),
            ],
        );

        // Act
        $this->tester->getClient()->resolveProductPriceTransferByPriceProductFilter([
            $this->createPriceProductTransfer(static::NET_PRICE, static::GROSS_PRICE, static::PRICE_TYPE_DEFAULT),
            $this->createPriceProductTransfer(static::NET_PRICE, static::GROSS_PRICE, static::PRICE_TYPE_ORIGINAL),
        ], new PriceProductFilterTransfer());
    }

    /**
     * @return void
     */
    public function testShouldExecutePriceProductPostResolvePluginStackInResolveProductPriceTransfer(): void
    {
        // Assert
        $this->tester->setDependency(
            PriceProductDependencyProvider::PLUGINS_PRICE_PRODUCT_POST_RESOLVE,
            [
                $this->getPriceProductPostResolvePluginMock(),
            ],
        );

        // Act
        $this->tester->getClient()->resolveProductPriceTransfer([
            $this->createPriceProductTransfer(static::NET_PRICE, static::GROSS_PRICE, static::PRICE_TYPE_DEFAULT),
            $this->createPriceProductTransfer(static::NET_PRICE, static::GROSS_PRICE, static::PRICE_TYPE_ORIGINAL),
        ]);
    }

    /**
     * @param \Generated\Shared\Transfer\CurrentProductPriceTransfer $currentProductPriceTransfer
     * @param string $volumePriceDataDefaultDefaultJson
     * @param string $volumePriceDataOriginJson
     *
     * @return void
     */
    protected function makeAsserts(
        CurrentProductPriceTransfer $currentProductPriceTransfer,
        string $volumePriceDataDefaultDefaultJson,
        string $volumePriceDataOriginJson
    ): void {
        $this->assertCount(2, $currentProductPriceTransfer->getPriceDataByPriceType());
        $this->assertSame(static::GROSS_PRICE, $currentProductPriceTransfer->getPrice());

        $this->assertSame(
            $currentProductPriceTransfer->getPriceData(),
            $volumePriceDataDefaultDefaultJson,
        );

        $priceDataByPriceType = $currentProductPriceTransfer->getPriceDataByPriceType();
        $this->assertSame(
            $priceDataByPriceType[static::PRICE_TYPE_DEFAULT],
            $volumePriceDataDefaultDefaultJson,
        );

        $this->assertSame(
            $priceDataByPriceType[static::PRICE_TYPE_ORIGINAL],
            $volumePriceDataOriginJson,
        );
    }

    /**
     * @param int $netPrice
     * @param int $grossPrice
     * @param string $priceType
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    protected function createPriceProductTransfer(int $netPrice, int $grossPrice, string $priceType): PriceProductTransfer
    {
        $currencyTransfer = (new CurrencyTransfer())
            ->setCode(static::CURRENCY_CODE);

        $moneyValueTransfer = (new MoneyValueTransfer())
            ->setNetAmount($netPrice)
            ->setGrossAmount($grossPrice)
            ->setCurrency($currencyTransfer);

        $priceProductTransfer = (new PriceProductTransfer())
            ->setPriceTypeName($priceType)
            ->setMoneyValue($moneyValueTransfer);

        return $priceProductTransfer;
    }

    /**
     * @return array<int>
     */
    protected function getVolumePriceDataDefault(): array
    {
        return [
            [
                static::VOLUME_PRICE_QUANTITY => 5,
                static::VOLUME_PRICE_NET_PRICE => 100,
                static::VOLUME_PRICE_GROSS_PRICE => 95,
            ],
        ];
    }

    /**
     * @return array<int>
     */
    protected function getVolumePriceDataOrigin(): array
    {
        return [
            [
                static::VOLUME_PRICE_QUANTITY => 5,
                static::VOLUME_PRICE_NET_PRICE => 110,
                static::VOLUME_PRICE_GROSS_PRICE => 105,
            ],
        ];
    }

    /**
     * @return \Spryker\Client\StoreExtension\Dependency\Plugin\StoreExpanderPluginInterface
     */
    protected function createStoreStorageStoreExpanderPluginMock(): StoreExpanderPluginInterface
    {
        $storeStorageStoreExpanderPluginMock = $this->createMock(StoreExpanderPluginInterface::class);
        $storeStorageStoreExpanderPluginMock->method('expand')
            ->willReturn((new StoreTransfer())
                ->setName(static::DEFAULT_STORE)
                ->setDefaultCurrencyIsoCode(static::CURRENCY_CODE));

        return $storeStorageStoreExpanderPluginMock;
    }

    /**
     * @return \Spryker\Client\PriceProductExtension\Dependency\Plugin\PriceProductPostResolvePluginInterface
     */
    protected function getPriceProductPostResolvePluginMock(): PriceProductPostResolvePluginInterface
    {
        $priceProductPostResolvePluginMock = $this
            ->getMockBuilder(PriceProductPostResolvePluginInterface::class)
            ->getMock();

        $priceProductPostResolvePluginMock
            ->expects($this->once())
            ->method('postResolve')
            ->willReturnCallback(function (PriceProductTransfer $priceProductTransfer) {
                return $priceProductTransfer;
            });

        return $priceProductPostResolvePluginMock;
    }
}
