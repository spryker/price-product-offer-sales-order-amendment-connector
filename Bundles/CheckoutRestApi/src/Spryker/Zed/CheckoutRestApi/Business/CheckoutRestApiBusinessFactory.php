<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CheckoutRestApi\Business;

use Spryker\Shared\CheckoutExtension\CheckoutExtensionContextsInterface;
use Spryker\Shared\Kernel\StrategyResolver;
use Spryker\Shared\Kernel\StrategyResolverInterface;
use Spryker\Shared\SalesOrderAmendmentExtension\SalesOrderAmendmentExtensionContextsInterface;
use Spryker\Zed\CheckoutRestApi\Business\Checkout\Address\AddressReader;
use Spryker\Zed\CheckoutRestApi\Business\Checkout\Address\AddressReaderInterface;
use Spryker\Zed\CheckoutRestApi\Business\Checkout\CheckoutDataReader;
use Spryker\Zed\CheckoutRestApi\Business\Checkout\CheckoutDataReaderInterface;
use Spryker\Zed\CheckoutRestApi\Business\Checkout\PlaceOrderProcessor;
use Spryker\Zed\CheckoutRestApi\Business\Checkout\PlaceOrderProcessorInterface;
use Spryker\Zed\CheckoutRestApi\Business\Checkout\Quote\QuoteReader;
use Spryker\Zed\CheckoutRestApi\Business\Checkout\Quote\QuoteReaderInterface;
use Spryker\Zed\CheckoutRestApi\Business\Expander\CheckoutExpander;
use Spryker\Zed\CheckoutRestApi\Business\Expander\CheckoutExpanderInterface;
use Spryker\Zed\CheckoutRestApi\Business\Validator\CheckoutValidator;
use Spryker\Zed\CheckoutRestApi\Business\Validator\CheckoutValidatorInterface;
use Spryker\Zed\CheckoutRestApi\CheckoutRestApiDependencyProvider;
use Spryker\Zed\CheckoutRestApi\Dependency\Facade\CheckoutRestApiToCalculationFacadeInterface;
use Spryker\Zed\CheckoutRestApi\Dependency\Facade\CheckoutRestApiToCartFacadeInterface;
use Spryker\Zed\CheckoutRestApi\Dependency\Facade\CheckoutRestApiToCartsRestApiFacadeInterface;
use Spryker\Zed\CheckoutRestApi\Dependency\Facade\CheckoutRestApiToCheckoutFacadeInterface;
use Spryker\Zed\CheckoutRestApi\Dependency\Facade\CheckoutRestApiToCustomerFacadeInterface;
use Spryker\Zed\CheckoutRestApi\Dependency\Facade\CheckoutRestApiToPaymentFacadeInterface;
use Spryker\Zed\CheckoutRestApi\Dependency\Facade\CheckoutRestApiToQuoteFacadeInterface;
use Spryker\Zed\CheckoutRestApi\Dependency\Facade\CheckoutRestApiToShipmentFacadeInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\CheckoutRestApi\CheckoutRestApiConfig getConfig()
 */
class CheckoutRestApiBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\CheckoutRestApi\Business\Checkout\CheckoutDataReaderInterface
     */
    public function createCheckoutDataReader(): CheckoutDataReaderInterface
    {
        return new CheckoutDataReader(
            $this->getCalculationFacade(),
            $this->createCheckoutValidator(),
            $this->createCheckoutExpander(),
            $this->getConfig(),
            $this->getQuoteMapperPlugins(),
        );
    }

    /**
     * @return \Spryker\Zed\CheckoutRestApi\Business\Checkout\PlaceOrderProcessorInterface
     */
    public function createPlaceOrderProcessor(): PlaceOrderProcessorInterface
    {
        return new PlaceOrderProcessor(
            $this->getCheckoutFacade(),
            $this->getQuoteFacade(),
            $this->getCalculationFacade(),
            $this->createCheckoutValidator(),
            $this->getQuoteMapperPlugins(),
            $this->getConfig(),
        );
    }

    /**
     * @return \Spryker\Zed\CheckoutRestApi\Business\Validator\CheckoutValidatorInterface
     */
    public function createCheckoutValidator(): CheckoutValidatorInterface
    {
        return new CheckoutValidator(
            $this->createQuoteReader(),
            $this->getCartFacade(),
            $this->createCheckoutDataValidatorPluginStrategyResolver(),
            $this->getReadCheckoutDataValidatorPlugins(),
        );
    }

    /**
     * @return \Spryker\Shared\Kernel\StrategyResolverInterface<list<\Spryker\Zed\CheckoutRestApiExtension\Dependency\Plugin\CheckoutDataValidatorPluginInterface>>
     */
    public function createCheckoutDataValidatorPluginStrategyResolver(): StrategyResolverInterface
    {
        return new StrategyResolver(
            [
                CheckoutExtensionContextsInterface::CONTEXT_CHECKOUT => $this->getProvidedDependency(CheckoutRestApiDependencyProvider::PLUGINS_CHECKOUT_DATA_VALIDATOR, static::LOADING_LAZY),
                SalesOrderAmendmentExtensionContextsInterface::CONTEXT_ORDER_AMENDMENT => $this->getProvidedDependency(CheckoutRestApiDependencyProvider::PLUGINS_CHECKOUT_DATA_VALIDATOR_FOR_ORDER_AMENDMENT, static::LOADING_LAZY),
            ],
            CheckoutExtensionContextsInterface::CONTEXT_CHECKOUT,
        );
    }

    /**
     * @return \Spryker\Zed\CheckoutRestApi\Business\Checkout\Quote\QuoteReaderInterface
     */
    public function createQuoteReader(): QuoteReaderInterface
    {
        return new QuoteReader($this->getCartsRestApiFacade());
    }

    /**
     * @return \Spryker\Zed\CheckoutRestApi\Business\Checkout\Address\AddressReaderInterface
     */
    public function createAddressReader(): AddressReaderInterface
    {
        return new AddressReader($this->getCustomerFacade());
    }

    /**
     * @return \Spryker\Zed\CheckoutRestApi\Business\Expander\CheckoutExpanderInterface
     */
    public function createCheckoutExpander(): CheckoutExpanderInterface
    {
        return new CheckoutExpander(
            $this->getShipmentFacade(),
            $this->getPaymentFacade(),
            $this->createAddressReader(),
            $this->getCheckoutDataExpanderPlugins(),
        );
    }

    /**
     * @return \Spryker\Zed\CheckoutRestApi\Dependency\Facade\CheckoutRestApiToCartFacadeInterface
     */
    public function getCartFacade(): CheckoutRestApiToCartFacadeInterface
    {
        return $this->getProvidedDependency(CheckoutRestApiDependencyProvider::FACADE_CART);
    }

    /**
     * @return \Spryker\Zed\CheckoutRestApi\Dependency\Facade\CheckoutRestApiToCartsRestApiFacadeInterface
     */
    public function getCartsRestApiFacade(): CheckoutRestApiToCartsRestApiFacadeInterface
    {
        return $this->getProvidedDependency(CheckoutRestApiDependencyProvider::FACADE_CARTS_REST_API);
    }

    /**
     * @return \Spryker\Zed\CheckoutRestApi\Dependency\Facade\CheckoutRestApiToCheckoutFacadeInterface
     */
    public function getCheckoutFacade(): CheckoutRestApiToCheckoutFacadeInterface
    {
        return $this->getProvidedDependency(CheckoutRestApiDependencyProvider::FACADE_CHECKOUT);
    }

    /**
     * @return \Spryker\Zed\CheckoutRestApi\Dependency\Facade\CheckoutRestApiToCustomerFacadeInterface
     */
    public function getCustomerFacade(): CheckoutRestApiToCustomerFacadeInterface
    {
        return $this->getProvidedDependency(CheckoutRestApiDependencyProvider::FACADE_CUSTOMER);
    }

    /**
     * @return \Spryker\Zed\CheckoutRestApi\Dependency\Facade\CheckoutRestApiToPaymentFacadeInterface
     */
    public function getPaymentFacade(): CheckoutRestApiToPaymentFacadeInterface
    {
        return $this->getProvidedDependency(CheckoutRestApiDependencyProvider::FACADE_PAYMENT);
    }

    /**
     * @return \Spryker\Zed\CheckoutRestApi\Dependency\Facade\CheckoutRestApiToQuoteFacadeInterface
     */
    public function getQuoteFacade(): CheckoutRestApiToQuoteFacadeInterface
    {
        return $this->getProvidedDependency(CheckoutRestApiDependencyProvider::FACADE_QUOTE);
    }

    /**
     * @return \Spryker\Zed\CheckoutRestApi\Dependency\Facade\CheckoutRestApiToShipmentFacadeInterface
     */
    public function getShipmentFacade(): CheckoutRestApiToShipmentFacadeInterface
    {
        return $this->getProvidedDependency(CheckoutRestApiDependencyProvider::FACADE_SHIPMENT);
    }

    /**
     * @return \Spryker\Zed\CheckoutRestApi\Dependency\Facade\CheckoutRestApiToCalculationFacadeInterface
     */
    public function getCalculationFacade(): CheckoutRestApiToCalculationFacadeInterface
    {
        return $this->getProvidedDependency(CheckoutRestApiDependencyProvider::FACADE_CALCULATION);
    }

    /**
     * @return array<\Spryker\Zed\CheckoutRestApiExtension\Dependency\Plugin\QuoteMapperPluginInterface>
     */
    public function getQuoteMapperPlugins(): array
    {
        return $this->getProvidedDependency(CheckoutRestApiDependencyProvider::PLUGINS_QUOTE_MAPPER);
    }

    /**
     * @return array<\Spryker\Zed\CheckoutRestApiExtension\Dependency\Plugin\ReadCheckoutDataValidatorPluginInterface>
     */
    public function getReadCheckoutDataValidatorPlugins(): array
    {
        return $this->getProvidedDependency(CheckoutRestApiDependencyProvider::PLUGINS_READ_CHECKOUT_DATA_VALIDATOR);
    }

    /**
     * @return array<\Spryker\Zed\CheckoutRestApiExtension\Dependency\Plugin\CheckoutDataExpanderPluginInterface>
     */
    public function getCheckoutDataExpanderPlugins(): array
    {
        return $this->getProvidedDependency(CheckoutRestApiDependencyProvider::PLUGINS_CHECKOUT_DATA_EXPANDER);
    }
}
