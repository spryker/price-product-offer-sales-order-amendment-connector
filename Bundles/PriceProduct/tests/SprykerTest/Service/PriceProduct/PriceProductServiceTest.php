<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Service\PriceProduct;

use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\MoneyValueBuilder;
use Generated\Shared\DataBuilder\PriceProductBuilder;
use Generated\Shared\DataBuilder\PriceProductFilterBuilder;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\MoneyValueTransfer;
use Generated\Shared\Transfer\PriceProductCriteriaTransfer;
use Generated\Shared\Transfer\PriceProductDimensionTransfer;
use Generated\Shared\Transfer\PriceProductFilterTransfer;
use Generated\Shared\Transfer\PriceProductTransfer;
use Spryker\Service\PriceProduct\PriceProductServiceInterface;
use Spryker\Shared\PriceProduct\PriceProductConstants;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Service
 * @group PriceProduct
 * @group PriceProductServiceTest
 * Add your own group annotations below this line
 */
class PriceProductServiceTest extends Unit
{
    /**
     * @var string
     */
    protected const PRICE_TYPE_DEFAULT = 'DEFAULT';

    /**
     * @var string
     */
    protected const CURRENCY_ISO_CODE = 'EUR';

    /**
     * @uses \Spryker\Shared\Price\PriceConfig::PRICE_MODE_GROSS
     *
     * @var string
     */
    protected const PRICE_MODE_GROSS = 'GROSS_MODE';

    /**
     * @uses \Spryker\Shared\Price\PriceConfig::PRICE_MODE_NET
     *
     * @var string
     */
    protected const PRICE_MODE_NET = 'NET_MODE';

    /**
     * @var string
     */
    protected const TEST_DIMENSION = 'TEST_DIMENSION';

    /**
     * @var int
     */
    protected const TEST_ID_CURRENCY = 1;

    /**
     * @var int
     */
    protected const TEST_FK_STORE = 1;

    /**
     * @var string
     */
    protected const EXPECTED_GROUP_KEY_EMPTY_DIMENSION = 'EUR-DEFAULT-1';

    /**
     * @var string
     */
    protected const EXPECTED_GROUP_KEY_DEFAULT_DIMENSION = 'EUR-DEFAULT-1-PRICE_DIMENSION_DEFAULT';

    /**
     * @var \SprykerTest\Service\PriceProduct\PriceProductTester
     */
    protected $tester;

    /**
     * @dataProvider getPriceProductTransfersWithAllData
     *
     * @param array<\Generated\Shared\Transfer\PriceProductTransfer> $concretePriceProductTransfers
     * @param array<\Generated\Shared\Transfer\PriceProductTransfer> $abstractPriceProductTransfers
     *
     * @return void
     */
    public function testMergePricesWillReturnConcretePricesOnConcretePriceSet(
        array $concretePriceProductTransfers,
        array $abstractPriceProductTransfers
    ): void {
        $abstractPriceProductTransfers = $this->prefillTransferWithDataForPriceGrouping($abstractPriceProductTransfers);
        $concretePriceProductTransfers = $this->prefillTransferWithDataForPriceGrouping($concretePriceProductTransfers);
        $priceProductService = $this->getPriceProductService();

        $mergedPriceProductTransfers = $priceProductService->mergeConcreteAndAbstractPrices($abstractPriceProductTransfers, $concretePriceProductTransfers);

        /** @var \Generated\Shared\Transfer\PriceProductTransfer $concretePriceProductTransfer */
        $concretePriceProductTransfer = $concretePriceProductTransfers[0];
        /** @var \Generated\Shared\Transfer\PriceProductTransfer $mergedPriceProductTransfer */
        $mergedPriceProductTransfer = $mergedPriceProductTransfers[array_keys($mergedPriceProductTransfers)[0]];
        $this->assertSame($concretePriceProductTransfer, $mergedPriceProductTransfer);
        $this->assertEquals($concretePriceProductTransfer->getMoneyValue()->getGrossAmount(), $mergedPriceProductTransfer->getMoneyValue()->getGrossAmount());
        $this->assertEquals($concretePriceProductTransfer->getMoneyValue()->getNetAmount(), $mergedPriceProductTransfer->getMoneyValue()->getNetAmount());
        /** @var \Generated\Shared\Transfer\PriceProductTransfer $abstractPriceProductTransfer */
        $abstractPriceProductTransfer = $abstractPriceProductTransfers[0];
        $this->assertNotEquals($abstractPriceProductTransfer->getMoneyValue()->getGrossAmount(), $mergedPriceProductTransfer->getMoneyValue()->getGrossAmount());
        $this->assertNotEquals($abstractPriceProductTransfer->getMoneyValue()->getNetAmount(), $mergedPriceProductTransfer->getMoneyValue()->getNetAmount());
    }

    /**
     * @dataProvider getPriceProductTransfersWithPartialConcreteData
     *
     * @param array<\Generated\Shared\Transfer\PriceProductTransfer> $concretePriceProductTransfers
     * @param array<\Generated\Shared\Transfer\PriceProductTransfer> $abstractPriceProductTransfers
     *
     * @return void
     */
    public function testMergePricesWillReturnAbstractPricesOnConcretePriceNotSet(
        array $concretePriceProductTransfers,
        array $abstractPriceProductTransfers
    ): void {
        $abstractPriceProductTransfers = $this->prefillTransferWithDataForPriceGrouping($abstractPriceProductTransfers);
        $concretePriceProductTransfers = $this->prefillTransferWithDataForPriceGrouping($concretePriceProductTransfers);

        $priceProductService = $this->getPriceProductService();

        /** @var \Generated\Shared\Transfer\PriceProductTransfer $concretePriceProductTransfer */
        $concretePriceProductTransfer = $concretePriceProductTransfers[0];
        $concretePriceProductTransfer->getMoneyValue()->setGrossAmount(null)->setNetAmount(null);

        $mergedPriceProductTransfers = $priceProductService->mergeConcreteAndAbstractPrices($abstractPriceProductTransfers, $concretePriceProductTransfers);

        /** @var \Generated\Shared\Transfer\PriceProductTransfer $mergedPriceProductTransfer */
        $mergedPriceProductTransfer = $mergedPriceProductTransfers[array_keys($mergedPriceProductTransfers)[1]];
        /** @var \Generated\Shared\Transfer\PriceProductTransfer $abstractPriceProductTransfer */
        $abstractPriceProductTransfer = $abstractPriceProductTransfers[1];
        $this->assertSame($abstractPriceProductTransfer, $mergedPriceProductTransfer);
        $this->assertEquals($abstractPriceProductTransfer->getMoneyValue()->getGrossAmount(), $mergedPriceProductTransfer->getMoneyValue()->getGrossAmount());
        $this->assertEquals($abstractPriceProductTransfer->getMoneyValue()->getNetAmount(), $mergedPriceProductTransfer->getMoneyValue()->getNetAmount());
    }

    /**
     * @dataProvider getPriceProductTransfersWithPartialConcreteData
     *
     * @param array<\Generated\Shared\Transfer\PriceProductTransfer> $concretePriceProductTransfers
     * @param array<\Generated\Shared\Transfer\PriceProductTransfer> $abstractPriceProductTransfers
     *
     * @return void
     */
    public function testMergePricesWillReturnPartialAbstractPricesOnSingleConcretePriceSet(
        array $concretePriceProductTransfers,
        array $abstractPriceProductTransfers
    ): void {
        $abstractPriceProductTransfers = $this->prefillTransferWithDataForPriceGrouping($abstractPriceProductTransfers);
        $concretePriceProductTransfers = $this->prefillTransferWithDataForPriceGrouping($concretePriceProductTransfers);
        $priceProductService = $this->getPriceProductService();

        $mergedPriceProductTransfers = $priceProductService->mergeConcreteAndAbstractPrices($abstractPriceProductTransfers, $concretePriceProductTransfers);

        /** @var \Generated\Shared\Transfer\PriceProductTransfer $concretePriceProductTransfer */
        $concretePriceProductTransfer = $concretePriceProductTransfers[0];
        /** @var \Generated\Shared\Transfer\PriceProductTransfer $abstractPriceProductTransfer */
        $abstractPriceProductTransfer = $abstractPriceProductTransfers[0];
        /** @var \Generated\Shared\Transfer\PriceProductTransfer $mergedPriceProductTransfer */
        $mergedPriceProductTransfer = $mergedPriceProductTransfers[array_keys($mergedPriceProductTransfers)[0]];
        $this->assertSame($concretePriceProductTransfer, $mergedPriceProductTransfer);
        $this->assertEquals($concretePriceProductTransfer->getMoneyValue()->getGrossAmount(), $mergedPriceProductTransfer->getMoneyValue()->getGrossAmount());
        $this->assertEquals($concretePriceProductTransfer->getMoneyValue()->getNetAmount(), $mergedPriceProductTransfer->getMoneyValue()->getNetAmount());
        $this->assertNotEquals($abstractPriceProductTransfer->getMoneyValue()->getGrossAmount(), $mergedPriceProductTransfer->getMoneyValue()->getGrossAmount());
        $this->assertNotEquals($abstractPriceProductTransfer->getMoneyValue()->getNetAmount(), $mergedPriceProductTransfer->getMoneyValue()->getNetAmount());

        /** @var \Generated\Shared\Transfer\PriceProductTransfer $mergedPriceProductTransfer */
        $mergedPriceProductTransfer = $mergedPriceProductTransfers[array_keys($mergedPriceProductTransfers)[1]];
        /** @var \Generated\Shared\Transfer\PriceProductTransfer $abstractPriceProductTransfer */
        $abstractPriceProductTransfer = $abstractPriceProductTransfers[1];
        $this->assertSame($abstractPriceProductTransfer, $mergedPriceProductTransfer);
        $this->assertEquals($abstractPriceProductTransfer->getMoneyValue()->getGrossAmount(), $mergedPriceProductTransfer->getMoneyValue()->getGrossAmount());
        $this->assertEquals($abstractPriceProductTransfer->getMoneyValue()->getNetAmount(), $mergedPriceProductTransfer->getMoneyValue()->getNetAmount());
    }

    /**
     * @dataProvider getPriceProductTransfersWithMoreConcreteData
     *
     * @param array<\Generated\Shared\Transfer\PriceProductTransfer> $concretePriceProductTransfers
     * @param array<\Generated\Shared\Transfer\PriceProductTransfer> $abstractPriceProductTransfers
     *
     * @return void
     */
    public function testMergePricesWillReturnExtraConcretePriceSet(
        array $concretePriceProductTransfers,
        array $abstractPriceProductTransfers
    ): void {
        $abstractPriceProductTransfers = $this->prefillTransferWithDataForPriceGrouping($abstractPriceProductTransfers);
        $concretePriceProductTransfers = $this->prefillTransferWithDataForPriceGrouping($concretePriceProductTransfers);
        $priceProductService = $this->getPriceProductService();

        $mergedPriceProductTransfers = $priceProductService->mergeConcreteAndAbstractPrices($abstractPriceProductTransfers, $concretePriceProductTransfers);

        /** @var \Generated\Shared\Transfer\PriceProductTransfer $concretePriceProductTransfer */
        $concretePriceProductTransfer = $concretePriceProductTransfers[0];
        /** @var \Generated\Shared\Transfer\PriceProductTransfer $abstractPriceProductTransfer */
        $abstractPriceProductTransfer = $abstractPriceProductTransfers[0];
        /** @var \Generated\Shared\Transfer\PriceProductTransfer $mergedPriceProductTransfer */
        $mergedPriceProductTransfer = $mergedPriceProductTransfers[array_keys($mergedPriceProductTransfers)[0]];
        $this->assertSame($concretePriceProductTransfer, $mergedPriceProductTransfer);
        $this->assertEquals($concretePriceProductTransfer->getMoneyValue()->getGrossAmount(), $mergedPriceProductTransfer->getMoneyValue()->getGrossAmount());
        $this->assertEquals($concretePriceProductTransfer->getMoneyValue()->getNetAmount(), $mergedPriceProductTransfer->getMoneyValue()->getNetAmount());
        $this->assertNotEquals($abstractPriceProductTransfer->getMoneyValue()->getGrossAmount(), $mergedPriceProductTransfer->getMoneyValue()->getGrossAmount());
        $this->assertNotEquals($abstractPriceProductTransfer->getMoneyValue()->getNetAmount(), $mergedPriceProductTransfer->getMoneyValue()->getNetAmount());

        $this->assertCount(4, $mergedPriceProductTransfers);
    }

    /**
     * @return void
     */
    public function testResolveProductPriceByPriceProductCriteriaIgnoresPricesWithEmptyValueForRequestedPriceType(): void
    {
        // Arrange
        $priceWithNetAmount = (new PriceProductTransfer())
            ->setMoneyValue(
                (new MoneyValueTransfer())
                    ->setNetAmount(100)
                    ->setCurrency(
                        (new CurrencyTransfer())
                            ->setIdCurrency(static::TEST_ID_CURRENCY),
                    ),
            )
            ->setPriceTypeName(static::PRICE_TYPE_DEFAULT)
            ->setPriceDimension(
                (new PriceProductDimensionTransfer())
                    ->setType(static::TEST_DIMENSION),
            );

        $priceWithoutNetAmount = (new PriceProductTransfer())->fromArray($priceWithNetAmount->toArray());
        $priceWithoutNetAmount->setMoneyValue(
            (new MoneyValueTransfer())
                ->fromArray($priceWithNetAmount->getMoneyValue()->toArray())
                ->setNetAmount(null),
        );

        $priceProductTransfers = [
            $priceWithoutNetAmount,
            $priceWithNetAmount,
        ];

        $priceProductCriteriaTransfer = (new PriceProductCriteriaTransfer())
            ->setPriceMode(static::PRICE_MODE_NET)
            ->setPriceType(static::PRICE_TYPE_DEFAULT)
            ->setIdCurrency(static::TEST_ID_CURRENCY);

        // Act
        $result = $this->getPriceProductService()->resolveProductPriceByPriceProductCriteria($priceProductTransfers, $priceProductCriteriaTransfer);

        // Assert
        $this->assertSame($priceWithNetAmount, $result);
    }

    /**
     * @return void
     */
    public function testResolveProductPriceByPriceProductCriteriaReturnsNullIfThereAreNoPricesWithRequestedMode(): void
    {
        // Arrange
        $priceProductTransferGrossFirst = (new PriceProductTransfer())
            ->setMoneyValue(
                (new MoneyValueTransfer())
                    ->setGrossAmount(100)
                    ->setCurrency(
                        (new CurrencyTransfer())
                            ->setIdCurrency(static::TEST_ID_CURRENCY),
                    ),
            )
            ->setPriceTypeName(static::PRICE_TYPE_DEFAULT)
            ->setPriceDimension(
                (new PriceProductDimensionTransfer())
                    ->setType(static::TEST_DIMENSION),
            );

        $priceProductTransferGrossSecond = (new PriceProductTransfer())->fromArray($priceProductTransferGrossFirst->toArray());
        $priceProductTransferGrossSecond->setMoneyValue(
            (new MoneyValueTransfer())
                ->fromArray($priceProductTransferGrossFirst->getMoneyValue()->toArray())
                ->setGrossAmount(200),
        );

        $priceProductTransfers = [
            $priceProductTransferGrossFirst,
            $priceProductTransferGrossSecond,
        ];

        $priceProductCriteriaTransfer = (new PriceProductCriteriaTransfer())
            ->setPriceMode(static::PRICE_MODE_NET)
            ->setPriceType(static::PRICE_TYPE_DEFAULT)
            ->setIdCurrency(static::TEST_ID_CURRENCY);

        // Act
        $result = $this->getPriceProductService()->resolveProductPriceByPriceProductCriteria($priceProductTransfers, $priceProductCriteriaTransfer);

        // Assert
        $this->assertNull($result);
    }

    /**
     * @return void
     */
    public function testResolveProductPriceByPriceProductCriteriaReturnsPriceMatchedByCriteria(): void
    {
        // Arrange
        $priceProductTransferNet = (new PriceProductTransfer())
            ->setMoneyValue(
                (new MoneyValueTransfer())
                    ->setNetAmount(100)
                    ->setCurrency(
                        (new CurrencyTransfer())
                            ->setIdCurrency(static::TEST_ID_CURRENCY),
                    ),
            )
            ->setPriceTypeName(static::PRICE_TYPE_DEFAULT)
            ->setPriceDimension(
                (new PriceProductDimensionTransfer())
                    ->setType(static::TEST_DIMENSION),
            );

        $priceProductTransfers = [
            $priceProductTransferNet,
        ];

        $priceProductCriteriaTransfer = (new PriceProductCriteriaTransfer())
            ->setPriceMode(static::PRICE_MODE_NET)
            ->setPriceType(static::PRICE_TYPE_DEFAULT)
            ->setIdCurrency(static::TEST_ID_CURRENCY);

        // Act
        $result = $this->getPriceProductService()->resolveProductPriceByPriceProductCriteria($priceProductTransfers, $priceProductCriteriaTransfer);

        // Assert
        $this->assertSame($priceProductTransferNet, $result);
    }

    /**
     * @dataProvider getDifferentPriceModeProductPricesData
     *
     * @param array<\Generated\Shared\Transfer\PriceProductTransfer> $priceProductTransfers
     * @param int $expectedProductPrice
     * @param string $priceMode
     *
     * @return void
     */
    public function testProductPricesAreFilteredByPriceMode(
        array $priceProductTransfers,
        int $expectedProductPrice,
        string $priceMode
    ): void {
        // Arrange
        $priceProductFilterTransfer = $this->buildPriceProductFilterTransfer([
            'priceMode' => $priceMode,
        ]);

        // Act
        $resultPriceProductTransfer = $this->getPriceProductService()->resolveProductPriceByPriceProductFilter(
            $priceProductTransfers,
            $priceProductFilterTransfer,
        );

        // Assert
        $this->assertExpectedProductPrice(
            $resultPriceProductTransfer,
            $expectedProductPrice,
            $priceMode,
        );
    }

    /**
     * @return void
     */
    public function testBuildPriceProductGroupKeyUsesDefaultPriceProductProperties(): void
    {
        // Arrange
        $priceProductService = $this->getPriceProductService();
        $priceProductTransfer = $this->tester->createPriceProductTransfer(
            [PriceProductTransfer::PRICE_TYPE_NAME => static::PRICE_TYPE_DEFAULT],
            [MoneyValueTransfer::FK_STORE => static::TEST_FK_STORE],
            [CurrencyTransfer::CODE => static::CURRENCY_ISO_CODE],
        );
        $priceProductTransfer->setPriceDimension(new PriceProductDimensionTransfer());

        // Act
        $priceProductGroupKey = $priceProductService->buildPriceProductGroupKey($priceProductTransfer);

        // Assert
        $this->assertSame(static::EXPECTED_GROUP_KEY_EMPTY_DIMENSION, $priceProductGroupKey);
    }

    /**
     * @return void
     */
    public function testBuildPriceProductGroupKeyUsesPriceProductDimensionProperties(): void
    {
        // Arrange
        $priceProductService = $this->getPriceProductService();
        $priceProductTransfer = $this->tester->createPriceProductTransfer(
            [PriceProductTransfer::PRICE_TYPE_NAME => static::PRICE_TYPE_DEFAULT],
            [MoneyValueTransfer::FK_STORE => static::TEST_FK_STORE],
            [CurrencyTransfer::CODE => static::CURRENCY_ISO_CODE],
        );
        $priceProductTransfer->setPriceDimension(
            (new PriceProductDimensionTransfer())
                ->setType(PriceProductConstants::PRICE_DIMENSION_DEFAULT),
        );

        // Act
        $priceProductGroupKey = $priceProductService->buildPriceProductGroupKey($priceProductTransfer);

        // Assert
        $this->assertSame(static::EXPECTED_GROUP_KEY_DEFAULT_DIMENSION, $priceProductGroupKey);
    }

    /**
     * @return array
     */
    public function getDifferentPriceModeProductPricesData(): array
    {
        return [
            'min gross price' => [
                [
                    $this->buildPriceProductTransfer([
                        PriceProductTransfer::MONEY_VALUE => ['grossAmount' => 100, 'netAmount' => null],
                    ]),
                    $this->buildPriceProductTransfer([
                        PriceProductTransfer::MONEY_VALUE => ['grossAmount' => 90, 'netAmount' => null],
                    ]),
                    $this->buildPriceProductTransfer([
                        PriceProductTransfer::MONEY_VALUE => ['grossAmount' => null, 'netAmount' => 80],
                    ]),
                ],
                90,
                static::PRICE_MODE_GROSS,
            ],
            'min net price' => [
                [
                    $this->buildPriceProductTransfer([
                        PriceProductTransfer::MONEY_VALUE => ['grossAmount' => null, 'netAmount' => 110],
                    ]),
                    $this->buildPriceProductTransfer([
                        PriceProductTransfer::MONEY_VALUE => ['grossAmount' => 70, 'netAmount' => null],
                    ]),
                    $this->buildPriceProductTransfer([
                        PriceProductTransfer::MONEY_VALUE => ['grossAmount' => null, 'netAmount' => 100],
                    ]),
                ],
                100,
                static::PRICE_MODE_NET,
            ],
        ];
    }

    /**
     * @param \Generated\Shared\Transfer\PriceProductTransfer $priceProductTransfer
     * @param int $expectedProductPrice
     * @param string $priceMode
     *
     * @return void
     */
    protected function assertExpectedProductPrice(
        PriceProductTransfer $priceProductTransfer,
        int $expectedProductPrice,
        string $priceMode
    ): void {
        $moneyValueTransfer = $priceProductTransfer->getMoneyValueOrFail();

        if ($priceMode === static::PRICE_MODE_GROSS) {
            $this->assertEquals($expectedProductPrice, $moneyValueTransfer->getGrossAmount());

            return;
        }

        $this->assertEquals($expectedProductPrice, $moneyValueTransfer->getNetAmount());
    }

    /**
     * @return \Spryker\Service\PriceProduct\PriceProductServiceInterface
     */
    protected function getPriceProductService(): PriceProductServiceInterface
    {
        return $this->tester->getLocator()->priceProduct()->service();
    }

    /**
     * @return array<\Generated\Shared\Transfer\PriceProductTransfer|\Spryker\Shared\Kernel\Transfer\AbstractTransfer>
     */
    protected function getSinglePriceProductTransfers(): array
    {
        return [
            (new PriceProductBuilder(['priceTypeName' => 'DEFAULT']))
                ->withMoneyValue((new MoneyValueBuilder())->withCurrency())
                ->withPriceDimension()
                ->withPriceType()
                ->build(),
        ];
    }

    /**
     * @return array<\Generated\Shared\Transfer\PriceProductTransfer|\Spryker\Shared\Kernel\Transfer\AbstractTransfer>
     */
    protected function getMultiplePriceProductTransfers(): array
    {
        $chfCurrencyData = ['code' => 'CHF', 'name' => 'CHF', 'symbol' => 'CHF'];

        return [
            (new PriceProductBuilder(['priceTypeName' => 'DEFAULT']))
                ->withMoneyValue((new MoneyValueBuilder())->withCurrency())
                ->withPriceDimension()
                ->withPriceType()
                ->build(),
            (new PriceProductBuilder(['priceTypeName' => 'ORIGINAL']))
                ->withMoneyValue((new MoneyValueBuilder())->withCurrency())
                ->withPriceDimension()
                ->withPriceType()
                ->build(),
            (new PriceProductBuilder(['priceTypeName' => 'DEFAULT']))
                ->withMoneyValue((new MoneyValueBuilder())->withCurrency($chfCurrencyData))
                ->withPriceDimension()
                ->withPriceType()
                ->build(),
            (new PriceProductBuilder(['priceTypeName' => 'ORIGINAL']))
                ->withMoneyValue((new MoneyValueBuilder())->withCurrency($chfCurrencyData))
                ->withPriceDimension()
                ->withPriceType()
                ->build(),
        ];
    }

    /**
     * @return array
     */
    public function getPriceProductTransfersWithAllData(): array
    {
        return [
            [$this->getMultiplePriceProductTransfers(), $this->getMultiplePriceProductTransfers()],
        ];
    }

    /**
     * @return array
     */
    public function getPriceProductTransfersWithPartialConcreteData(): array
    {
        return [
            [$this->getSinglePriceProductTransfers(), $this->getMultiplePriceProductTransfers()],
        ];
    }

    /**
     * @return array
     */
    public function getPriceProductTransfersWithMoreConcreteData(): array
    {
        return [
            [$this->getMultiplePriceProductTransfers(), $this->getSinglePriceProductTransfers()],
        ];
    }

    /**
     * @param array<\Generated\Shared\Transfer\PriceProductTransfer> $priceProductTransfers
     *
     * @return array<\Generated\Shared\Transfer\PriceProductTransfer>
     */
    protected function prefillTransferWithDataForPriceGrouping(array $priceProductTransfers): array
    {
        foreach ($priceProductTransfers as $priceProductTransfer) {
            $priceProductTransfer->setIsMergeable(true)
                ->setGroupKey(
                    $this->getPriceProductService()->buildPriceProductGroupKey($priceProductTransfer),
                );
        }

        return $priceProductTransfers;
    }

    /**
     * @param array $priceProductDataSeed
     *
     * @return \Generated\Shared\Transfer\PriceProductTransfer
     */
    protected function buildPriceProductTransfer(array $priceProductDataSeed): PriceProductTransfer
    {
        $priceProductDataSeed = array_merge([PriceProductTransfer::PRICE_TYPE_NAME => static::PRICE_TYPE_DEFAULT], $priceProductDataSeed);
        $moneyValueBuilder = (new MoneyValueBuilder())->withCurrency();

        return (new PriceProductBuilder($priceProductDataSeed))
            ->withMoneyValue($moneyValueBuilder)
            ->withPriceDimension()
            ->withPriceType()
            ->build();
    }

    /**
     * @param array $priceProductFilterDataSeed
     *
     * @return \Generated\Shared\Transfer\PriceProductFilterTransfer
     */
    protected function buildPriceProductFilterTransfer(array $priceProductFilterDataSeed): PriceProductFilterTransfer
    {
        $priceProductFilterDataSeed = array_merge([
            PriceProductFilterTransfer::PRICE_TYPE_NAME => static::PRICE_TYPE_DEFAULT,
            PriceProductFilterTransfer::CURRENCY_ISO_CODE => static::CURRENCY_ISO_CODE,
        ], $priceProductFilterDataSeed);

        return (new PriceProductFilterBuilder($priceProductFilterDataSeed))->build();
    }
}
