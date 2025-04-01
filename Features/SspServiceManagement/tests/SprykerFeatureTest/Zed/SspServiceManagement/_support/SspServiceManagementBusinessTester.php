<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeatureTest\Zed\SspServiceManagement;

use Codeception\Actor;
use Generated\Shared\DataBuilder\AddressBuilder;
use Generated\Shared\DataBuilder\CustomerBuilder;
use Generated\Shared\DataBuilder\ItemBuilder;
use Generated\Shared\Transfer\CurrencyTransfer;
use Generated\Shared\Transfer\PaymentMethodTransfer;
use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use Generated\Shared\Transfer\TaxTotalTransfer;
use Generated\Shared\Transfer\TotalsTransfer;
use Orm\Zed\Country\Persistence\SpyCountry;
use Spryker\Shared\Price\PriceMode;

/**
 * Inherited Methods
 *
 * @method void wantTo($text)
 * @method void wantToTest($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause($vars = [])
 * @method \SprykerFeature\Zed\SspServiceManagement\Business\SspServiceManagementFacadeInterface getFacade()
 *
 *
 * @SuppressWarnings(PHPMD)
 */
class SspServiceManagementBusinessTester extends Actor
{
    use _generated\SspServiceManagementBusinessTesterActions;

    /**
     * @param \Generated\Shared\Transfer\PaymentMethodTransfer $paymentMethodTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function getValidBaseQuoteTransfer(PaymentMethodTransfer $paymentMethodTransfer): QuoteTransfer
    {
        $country = new SpyCountry();
        $country->setIso2Code('ix');
        $country->save();

        $currencyTransfer = (new CurrencyTransfer())->setCode('EUR');
        $billingAddress = (new AddressBuilder())->build();
        $shippingAddress = (new AddressBuilder())->build();
        $customerTransfer = (new CustomerBuilder())->build();
        $itemTransfer = (new ItemBuilder())
            ->withShipment()
            ->build();

        $paymentTransfer = (new PaymentTransfer())
            ->setPaymentProvider($paymentMethodTransfer->getPaymentProvider()->getPaymentProviderKey())
            ->setPaymentMethod($paymentMethodTransfer->getPaymentMethodKey())
            ->setPaymentMethodName($paymentMethodTransfer->getName())
            ->setPaymentProviderName($paymentMethodTransfer->getPaymentProvider()->getName())
            ->setPaymentSelection($paymentMethodTransfer->getPaymentMethodKey())
            ->setAmount(1337);

        $shipmentTransfer = (new ShipmentTransfer())
            ->setMethod(new ShipmentMethodTransfer())
            ->setShippingAddress($shippingAddress);

        $totalsTransfer = (new TotalsTransfer())
            ->setGrandTotal(1337)
            ->setSubtotal(337)
            ->setTaxTotal((new TaxTotalTransfer())->setAmount(10));

        $storeTransfer = $this->haveStore([StoreTransfer::NAME => 'DE']);

        return (new QuoteTransfer())
            ->setCurrency($currencyTransfer)
            ->setPriceMode(PriceMode::PRICE_MODE_GROSS)
            ->setShippingAddress($shippingAddress)
            ->setBillingAddress($billingAddress)
            ->setTotals($totalsTransfer)
            ->setCustomer($customerTransfer)
            ->setShipment($shipmentTransfer)
            ->addItem($itemTransfer)
            ->setPayment($paymentTransfer)
            ->setStore($storeTransfer);
    }
}
