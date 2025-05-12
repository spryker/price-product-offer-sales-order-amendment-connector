<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Country\Business;

use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\CheckoutDataBuilder;
use Generated\Shared\DataBuilder\CountryCollectionBuilder;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CheckoutDataTransfer;
use Generated\Shared\Transfer\CountryTransfer;
use Generated\Shared\Transfer\RestAddressTransfer;
use Generated\Shared\Transfer\RestShipmentsTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Orm\Zed\Country\Persistence\SpyCountry;
use Orm\Zed\Country\Persistence\SpyRegion;
use Psr\Log\LoggerInterface;
use Spryker\Zed\Country\Business\CountryFacade;
use Spryker\Zed\Country\Business\Exception\MissingCountryException;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Country
 * @group Business
 * @group Facade
 * @group CountryFacadeTest
 * Add your own group annotations below this line
 */
class CountryFacadeTest extends Unit
{
    /**
     * @var string
     */
    public const ISO2_CODE = 'qx';

    /**
     * @var string
     */
    public const ISO3_CODE = 'qxz';

    /**
     * @var string
     */
    protected const ISO2_COUNTRY_DE = 'DE';

    /**
     * @var string
     */
    protected const ISO2_COUNTRY_AT = 'AT';

    /**
     * @var string
     */
    protected const ISO2_COUNTRY_US = 'US';

    /**
     * @var string
     */
    protected const COUNTRY_NAME_DE = 'Germany';

    /**
     * @var string
     */
    protected const STORE_NAME_DE = 'DE';

    /**
     * @var string
     */
    protected const FAKE_ISO_2_CODE = 'FAKE_ISO_2_CODE';

    /**
     * @var \Spryker\Zed\Country\Business\CountryFacade
     */
    protected $countryFacade;

    /**
     * @var \SprykerTest\Zed\Country\CountryBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->tester->ensureCountryStoreDatabaseTableIsEmpty();

        $this->countryFacade = new CountryFacade();
    }

    /**
     * @return \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getMockLogger(): LoggerInterface
    {
        return $this->getMockBuilder(LoggerInterface::class)->getMock();
    }

    /**
     * @return void
     */
    public function testGetCountryByIso2CodeReturnsRightValue(): void
    {
        $country = new SpyCountry();
        $country->setIso2Code(static::ISO2_CODE);
        $country->setIso3Code(static::ISO3_CODE);

        $country->save();

        $result = $this->countryFacade->getCountryByIso2Code(static::ISO2_CODE);

        $this->assertInstanceOf(CountryTransfer::class, $result);
        $this->assertSame($country->getIdCountry(), $result->getIdCountry());
    }

    /**
     * @return void
     */
    public function testGetCountryByIso3CodeReturnsRightValue(): void
    {
        $country = new SpyCountry();
        $country->setIso2Code(static::ISO2_CODE);
        $country->setIso3Code(static::ISO3_CODE);

        $country->save();

        $result = $this->countryFacade->getCountryByIso3Code(static::ISO3_CODE);

        $this->assertInstanceOf(CountryTransfer::class, $result);
        $this->assertSame($country->getIdCountry(), $result->getIdCountry());
    }

    /**
     * @return void
     */
    public function testGetCountryByIso3CodeReturnsException(): void
    {
        $this->expectException(MissingCountryException::class);
        $this->countryFacade->getCountryByIso3Code(static::ISO3_CODE);
    }

    /**
     * @return void
     */
    public function testGetCountryByIso2CodeReturnsException(): void
    {
        $this->expectException(MissingCountryException::class);
        $this->countryFacade->getCountryByIso2Code(static::ISO2_CODE);
    }

    /**
     * @return void
     */
    public function testGetCountriesByCountryIso2CodesReturnsRightValue(): void
    {
        $country = new SpyCountry();
        $country->setIso2Code(static::ISO2_CODE);
        $country->save();

        $region = new SpyRegion();
        $region->setName('test');
        $region->setFkCountry($country->getIdCountry());
        $region->setIso2Code('TS');
        $region->save();

        $countryCollectionTransfer = (new CountryCollectionBuilder())->build()->addCountries(
            (new CountryTransfer())->setIso2Code($country->getIso2Code()),
        );

        $countryTransfer = $this->countryFacade->findCountriesByIso2Codes($countryCollectionTransfer);

        $this->assertSame('TS', $countryTransfer->getCountries()[0]->getRegions()[0]->getIso2Code());
    }

    /**
     * @return void
     */
    public function testCountryFacadeWillValidateCountryCheckoutWithoutErrors(): void
    {
        $checkoutDataTransfer = $this->prepareCheckoutDataTransferWithIso2Codes();
        $checkoutResponseTransfer = $this->countryFacade->validateCountryCheckoutData($checkoutDataTransfer);

        $this->assertTrue($checkoutResponseTransfer->getIsSuccess());
        $this->assertSame(0, $checkoutResponseTransfer->getErrors()->count());
    }

    /**
     * @return void
     */
    public function testValidateCountryCheckoutDataValidatesMultiShipmentParameters(): void
    {
        $checkoutDataTransfer = $this->prepareCheckoutDataTransferWithIso2Codes()
            ->setShippingAddress(null)
            ->addShipment(
                (new RestShipmentsTransfer())
                    ->setShippingAddress((new RestAddressTransfer())->setIso2Code(static::ISO2_COUNTRY_DE)),
            )
            ->addShipment(
                (new RestShipmentsTransfer())
                    ->setShippingAddress(new RestAddressTransfer()),
            );

        $checkoutResponseTransfer = $this->countryFacade->validateCountryCheckoutData($checkoutDataTransfer);

        $this->assertTrue($checkoutResponseTransfer->getIsSuccess());
        $this->assertSame(0, $checkoutResponseTransfer->getErrors()->count());
    }

    /**
     * @return void
     */
    public function testCountryFacadeWillValidateCountryCheckoutWithErrors(): void
    {
        $checkoutDataTransfer = $this->prepareCheckoutDataTransferWithOutIso2Codes();
        $checkoutResponseTransfer = $this->countryFacade->validateCountryCheckoutData($checkoutDataTransfer);

        $this->assertFalse($checkoutResponseTransfer->getIsSuccess());
        $this->assertGreaterThan(0, $checkoutResponseTransfer->getErrors()->count());
    }

    /**
     * @return void
     */
    public function testValidateCountriesInCheckoutDataValidatesWithErrors(): void
    {
        // Arrange
        $checkoutDataTransfer = $this->prepareCheckoutDataTransferWithUnknownIso2Code();

        // Act
        $checkoutResponseTransfer = $this->countryFacade->validateCountriesInCheckoutData($checkoutDataTransfer);

        // Assert
        $this->assertFalse($checkoutResponseTransfer->getIsSuccess());
        $this->assertGreaterThan(0, $checkoutResponseTransfer->getErrors()->count());
    }

    /**
     * @return void
     */
    public function testUpdateStoreCountriesWithAddingNewAndRemovingOldRelations(): void
    {
        // Arrange
        $storeTransfer = $this->tester->haveStore([StoreTransfer::NAME => static::STORE_NAME_DE]);
        $this->tester->deleteCountryStore($storeTransfer->getIdStoreOrFail());

        $idCountryDe = $this->tester->haveCountry([CountryTransfer::ISO2_CODE => static::ISO2_COUNTRY_DE])->getIdCountryOrFail();
        $idCountryUs = $this->tester->haveCountry([CountryTransfer::ISO2_CODE => static::ISO2_COUNTRY_US])->getIdCountryOrFail();
        $idCountryAt = $this->tester->haveCountry([CountryTransfer::ISO2_CODE => static::ISO2_COUNTRY_AT])->getIdCountryOrFail();

        $this->tester->haveCountryStore($storeTransfer->getIdStoreOrFail(), $idCountryDe);
        $this->tester->haveCountryStore($storeTransfer->getIdStoreOrFail(), $idCountryAt);

        $storeTransfer->setCountries([static::ISO2_COUNTRY_DE, static::ISO2_COUNTRY_AT]);

        // Act
        $storeResponseTransfer = $this->countryFacade->updateStoreCountries($storeTransfer);

        // Assert
        $this->assertTrue($storeResponseTransfer->getIsSuccessful());
        $this->assertTrue($this->tester->countryStoreExists($storeTransfer->getIdStoreOrFail(), $idCountryDe));
        $this->assertTrue($this->tester->countryStoreExists($storeTransfer->getIdStoreOrFail(), $idCountryAt));
        $this->assertFalse($this->tester->countryStoreExists($storeTransfer->getIdStoreOrFail(), $idCountryUs));
    }

    /**
     * @return void
     */
    public function testExpandStoreTransfersWithCountriesSuccessful(): void
    {
        // Arrange
        $storeTransfer = $this->tester->haveStore([StoreTransfer::NAME => static::STORE_NAME_DE]);
        $idCountry = $this->tester->haveCountry([CountryTransfer::ISO2_CODE => static::ISO2_COUNTRY_DE])->getIdCountryOrFail();
        $this->tester->haveCountryStore($storeTransfer->getIdStoreOrFail(), $idCountry);

        // Act
        $storeTransfers = $this->countryFacade->expandStoreTransfersWithCountries([
            $storeTransfer->getIdStoreOrFail() => $storeTransfer,
        ]);

        // Assert
        $this->assertEqualsCanonicalizing(
            [static::COUNTRY_NAME_DE],
            array_values($storeTransfers[$storeTransfer->getIdStoreOrFail()]->getCountryNames()),
        );
        $this->assertEqualsCanonicalizing(
            [static::ISO2_COUNTRY_DE],
            array_values($storeTransfers[$storeTransfer->getIdStoreOrFail()]->getCountries()),
        );
    }

    /**
     * @return void
     */
    public function testExpandStoreTransfersWithCountriesWithoutCountryStoreRelations(): void
    {
        // Arrange
        $storeTransferEu = $this->tester->haveStore([StoreTransfer::NAME => static::STORE_NAME_DE]);

        $this->tester->deleteCountryStore($storeTransferEu->getIdStore());

        // Act
        $storeTransfers = $this->countryFacade->expandStoreTransfersWithCountries([
            $storeTransferEu->getIdStoreOrFail() => $storeTransferEu,
        ]);

        // Assert
        $this->assertSame(
            [],
            array_values($storeTransfers[$storeTransferEu->getIdStoreOrFail()]->getCountryNames()),
        );
        $this->assertSame(
            [],
            array_values($storeTransfers[$storeTransferEu->getIdStoreOrFail()]->getCountries()),
        );
    }

    /**
     * @return \Generated\Shared\Transfer\CheckoutDataTransfer
     */
    protected function prepareCheckoutDataTransferWithIso2Codes(): CheckoutDataTransfer
    {
        /** @var \Generated\Shared\Transfer\CheckoutDataTransfer $checkoutDataTransfer */
        $checkoutDataTransfer = (new CheckoutDataBuilder())
            ->withBillingAddress(['billingAddress' => (new AddressTransfer())->setIso2Code(static::ISO2_COUNTRY_DE)])
            ->withShippingAddress(['shippingAddress' => (new AddressTransfer())->setIso2Code(static::ISO2_COUNTRY_DE)])
            ->build();

        return $checkoutDataTransfer;
    }

    /**
     * @return \Generated\Shared\Transfer\CheckoutDataTransfer
     */
    protected function prepareCheckoutDataTransferWithOutIso2Codes(): CheckoutDataTransfer
    {
        /** @var \Generated\Shared\Transfer\CheckoutDataTransfer $checkoutDataTransfer */
        $checkoutDataTransfer = (new CheckoutDataBuilder([
            CheckoutDataTransfer::BILLING_ADDRESS => null,
            CheckoutDataTransfer::SHIPPING_ADDRESS => null,
        ]))->build();

        return $checkoutDataTransfer;
    }

    /**
     * @return \Generated\Shared\Transfer\CheckoutDataTransfer
     */
    protected function prepareCheckoutDataTransferWithUnknownIso2Code(): CheckoutDataTransfer
    {
        /** @var \Generated\Shared\Transfer\CheckoutDataTransfer $checkoutDataTransfer */
        $checkoutDataTransfer = (new CheckoutDataBuilder())
            ->withBillingAddress([AddressTransfer::ISO2_CODE => static::FAKE_ISO_2_CODE])
            ->withShippingAddress()
            ->build();

        return $checkoutDataTransfer;
    }
}
