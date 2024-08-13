<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Product\Business;

use ArrayObject;
use Codeception\Test\Unit;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\LocalizedAttributesTransfer;
use Generated\Shared\Transfer\LocalizedUrlTransfer;
use Generated\Shared\Transfer\ProductAbstractTransfer;
use Generated\Shared\Transfer\ProductUrlTransfer;
use Spryker\Zed\Product\Business\Product\NameGenerator\ProductAbstractNameGeneratorInterface;
use Spryker\Zed\Product\Business\Product\Url\ProductUrlGenerator;
use Spryker\Zed\Product\Dependency\Facade\ProductToLocaleBridge;
use Spryker\Zed\Product\Dependency\Service\ProductToUtilTextBridge;
use Spryker\Zed\Product\ProductConfig;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Product
 * @group Business
 * @group ProductUrlGeneratorTest
 * Add your own group annotations below this line
 */
class ProductUrlGeneratorTest extends Unit
{
    /**
     * @var array
     */
    public const PRODUCT_NAME = [
        'en_US' => 'Product name en_US',
        'de_DE' => 'Product name de_DE',
    ];

    /**
     * @var int
     */
    public const ID_PRODUCT_ABSTRACT = 1;

    /**
     * @var \Spryker\Zed\Product\Dependency\Facade\ProductToLocaleInterface
     */
    protected $localeFacade;

    /**
     * @var \Spryker\Zed\Product\Dependency\Service\ProductToUtilTextInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $utilTextService;

    /**
     * @var \Spryker\Zed\Product\Business\Product\NameGenerator\ProductAbstractNameGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productAbstractNameGenerator;

    /**
     * @var \Spryker\Zed\Product\Persistence\ProductQueryContainer
     */
    protected $productQueryContainer;

    /**
     * @var \Generated\Shared\Transfer\ProductAbstractTransfer
     */
    protected $productAbstractTransfer;

    /**
     * @var array<\Generated\Shared\Transfer\LocaleTransfer>
     */
    protected $locales;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->setupLocales();
        $this->setupProductAbstract();

        $this->localeFacade = $this->getMockBuilder(ProductToLocaleBridge::class)
        ->disableOriginalConstructor()->getMock();

        $this->utilTextService = $this->getMockBuilder(ProductToUtilTextBridge::class)
        ->disableOriginalConstructor()->getMock();

        $availableLocalesCollection = [
        $this->locales['de_DE']->getLocaleName() => $this->locales['de_DE'],
        $this->locales['en_US']->getLocaleName() => $this->locales['en_US'],
        ];

        $this->localeFacade
        ->expects($this->once())
        ->method('getLocaleCollection')
        ->willReturn($availableLocalesCollection);

        $this->productAbstractNameGenerator = $this->getMockBuilder(ProductAbstractNameGeneratorInterface::class)
        ->disableOriginalConstructor()->getMock();

        $this->productAbstractNameGenerator
        ->expects($this->exactly(2))
        ->method('getLocalizedProductAbstractName')
        ->withConsecutive(
            [
                $this->productAbstractTransfer,
                $this->locales['de_DE'],
            ],
            [
                $this->productAbstractTransfer,
                $this->locales['en_US'],
            ],
        )
        ->willReturn(static::PRODUCT_NAME['de_DE'], static::PRODUCT_NAME['en_US']);
    }

    /**
     * @return void
     */
    protected function setupLocales(): void
    {
        $this->locales['de_DE'] = new LocaleTransfer();
        $this->locales['de_DE']
        ->setIdLocale(46)
        ->setLocaleName('de_DE');

        $this->locales['en_US'] = new LocaleTransfer();
        $this->locales['en_US']
        ->setIdLocale(66)
        ->setLocaleName('en_US');
    }

    /**
     * @return void
     */
    protected function setupProductAbstract(): void
    {
        $this->productAbstractTransfer = new ProductAbstractTransfer();
        $this->productAbstractTransfer
        ->setSku('foo')
        ->setIdProductAbstract(static::ID_PRODUCT_ABSTRACT);

        $localizedAttribute = new LocalizedAttributesTransfer();
        $localizedAttribute
        ->setName(static::PRODUCT_NAME['de_DE'])
        ->setLocale($this->locales['de_DE']);

        $this->productAbstractTransfer->addLocalizedAttributes($localizedAttribute);

        $localizedAttribute = new LocalizedAttributesTransfer();
        $localizedAttribute
        ->setName(static::PRODUCT_NAME['en_US'])
        ->setLocale($this->locales['en_US']);

        $this->productAbstractTransfer->addLocalizedAttributes($localizedAttribute);
    }

    /**
     * @return void
     */
    public function testGetProductUrlShouldReturnTransfer(): void
    {
        $expectedDEUrl = (new LocalizedUrlTransfer())
        ->setLocale($this->locales['de_DE'])
        ->setUrl('/de-de/product-name-dede-1');

        $expectedENUrl = (new LocalizedUrlTransfer())
        ->setLocale($this->locales['en_US'])
        ->setUrl('/en-us/product-name-enus-1');

        $productUrlExpected = (new ProductUrlTransfer())
        ->setAbstractSku(
            $this->productAbstractTransfer->getSku(),
        )
        ->setUrls(
            new ArrayObject([$expectedDEUrl, $expectedENUrl]),
        );

        $this->utilTextService
        ->expects($this->exactly(2))
        ->method('generateSlug')
        ->withConsecutive([static::PRODUCT_NAME['de_DE']], [static::PRODUCT_NAME['en_US']])
        ->willReturnOnConsecutiveCalls('product-name-dede', 'product-name-enus');

        $configMock = $this->createMock(ProductConfig::class);
        $configMock->method('isFullLocaleNamesInUrlEnabled')->willReturn(true);

        $urlGenerator = new ProductUrlGenerator($this->productAbstractNameGenerator, $this->localeFacade, $this->utilTextService, $configMock);
        $productUrl = $urlGenerator->generateProductUrl($this->productAbstractTransfer);

        $this->assertSame($productUrlExpected->getAbstractSku(), $productUrl->getAbstractSku());
        $this->assertEquals($productUrlExpected, $productUrl);
    }

    /**
     * @return void
     */
    public function testGetProductUrlShouldReturnTransferBCCheck(): void
    {
        $expectedDEUrl = (new LocalizedUrlTransfer())
            ->setLocale($this->locales['de_DE'])
            ->setUrl('/de/product-name-dede-1');

        $expectedENUrl = (new LocalizedUrlTransfer())
            ->setLocale($this->locales['en_US'])
            ->setUrl('/en/product-name-enus-1');

        $productUrlExpected = (new ProductUrlTransfer())
            ->setAbstractSku(
                $this->productAbstractTransfer->getSku(),
            )
            ->setUrls(
                new ArrayObject([$expectedDEUrl, $expectedENUrl]),
            );

        $this->utilTextService
            ->expects($this->exactly(2))
            ->method('generateSlug')
            ->withConsecutive([static::PRODUCT_NAME['de_DE']], [static::PRODUCT_NAME['en_US']])
            ->willReturnOnConsecutiveCalls('product-name-dede', 'product-name-enus');

        $configMock = $this->createMock(ProductConfig::class);
        $configMock->method('isFullLocaleNamesInUrlEnabled')->willReturn(false);

        $urlGenerator = new ProductUrlGenerator($this->productAbstractNameGenerator, $this->localeFacade, $this->utilTextService, $configMock);
        $productUrl = $urlGenerator->generateProductUrl($this->productAbstractTransfer);

        $this->assertSame($productUrlExpected->getAbstractSku(), $productUrl->getAbstractSku());
        $this->assertEquals($productUrlExpected, $productUrl);
    }
}
