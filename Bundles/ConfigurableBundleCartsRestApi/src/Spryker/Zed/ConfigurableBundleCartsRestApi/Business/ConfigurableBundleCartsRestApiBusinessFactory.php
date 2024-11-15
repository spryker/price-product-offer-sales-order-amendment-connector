<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ConfigurableBundleCartsRestApi\Business;

use Spryker\Zed\ConfigurableBundleCartsRestApi\Business\Checker\QuotePermissionChecker;
use Spryker\Zed\ConfigurableBundleCartsRestApi\Business\Checker\QuotePermissionCheckerInterface;
use Spryker\Zed\ConfigurableBundleCartsRestApi\Business\Mapper\ConfiguredBundleMapper;
use Spryker\Zed\ConfigurableBundleCartsRestApi\Business\Mapper\ConfiguredBundleMapperInterface;
use Spryker\Zed\ConfigurableBundleCartsRestApi\Business\Writer\ConfiguredBundleWriter;
use Spryker\Zed\ConfigurableBundleCartsRestApi\Business\Writer\ConfiguredBundleWriterInterface;
use Spryker\Zed\ConfigurableBundleCartsRestApi\Business\Writer\GuestConfiguredBundleWriter;
use Spryker\Zed\ConfigurableBundleCartsRestApi\Business\Writer\GuestConfiguredBundleWriterInterface;
use Spryker\Zed\ConfigurableBundleCartsRestApi\ConfigurableBundleCartsRestApiDependencyProvider;
use Spryker\Zed\ConfigurableBundleCartsRestApi\Dependency\Facade\ConfigurableBundleCartsRestApiToCartsRestApiFacadeInterface;
use Spryker\Zed\ConfigurableBundleCartsRestApi\Dependency\Facade\ConfigurableBundleCartsRestApiToPersistentCartFacadeInterface;
use Spryker\Zed\ConfigurableBundleCartsRestApi\Dependency\Facade\ConfigurableBundleCartsRestApiToStoreFacadeInterface;
use Spryker\Zed\ConfigurableBundleCartsRestApi\Dependency\Service\ConfigurableBundleCartsRestApiToConfigurableBundleServiceInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\ConfigurableBundleCartsRestApi\ConfigurableBundleCartsRestApiConfig getConfig()
 */
class ConfigurableBundleCartsRestApiBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\ConfigurableBundleCartsRestApi\Business\Writer\ConfiguredBundleWriterInterface
     */
    public function createConfiguredBundleWriter(): ConfiguredBundleWriterInterface
    {
        return new ConfiguredBundleWriter(
            $this->getPersistentCartFacade(),
            $this->getCartsRestApiFacade(),
            $this->createConfiguredBundleMapper(),
            $this->createQuotePermissionChecker(),
        );
    }

    /**
     * @return \Spryker\Zed\ConfigurableBundleCartsRestApi\Business\Writer\GuestConfiguredBundleWriterInterface
     */
    public function createGuestConfiguredBundleWriter(): GuestConfiguredBundleWriterInterface
    {
        return new GuestConfiguredBundleWriter(
            $this->getPersistentCartFacade(),
            $this->getCartsRestApiFacade(),
            $this->createConfiguredBundleMapper(),
            $this->createQuotePermissionChecker(),
            $this->getStoreFacade(),
        );
    }

    /**
     * @return \Spryker\Zed\ConfigurableBundleCartsRestApi\Business\Mapper\ConfiguredBundleMapperInterface
     */
    public function createConfiguredBundleMapper(): ConfiguredBundleMapperInterface
    {
        return new ConfiguredBundleMapper(
            $this->getConfigurableBundleService(),
        );
    }

    /**
     * @return \Spryker\Zed\ConfigurableBundleCartsRestApi\Business\Checker\QuotePermissionCheckerInterface
     */
    public function createQuotePermissionChecker(): QuotePermissionCheckerInterface
    {
        return new QuotePermissionChecker();
    }

    /**
     * @return \Spryker\Zed\ConfigurableBundleCartsRestApi\Dependency\Facade\ConfigurableBundleCartsRestApiToPersistentCartFacadeInterface
     */
    public function getPersistentCartFacade(): ConfigurableBundleCartsRestApiToPersistentCartFacadeInterface
    {
        return $this->getProvidedDependency(ConfigurableBundleCartsRestApiDependencyProvider::FACADE_PERSISTENT_CART);
    }

    /**
     * @return \Spryker\Zed\ConfigurableBundleCartsRestApi\Dependency\Facade\ConfigurableBundleCartsRestApiToCartsRestApiFacadeInterface
     */
    public function getCartsRestApiFacade(): ConfigurableBundleCartsRestApiToCartsRestApiFacadeInterface
    {
        return $this->getProvidedDependency(ConfigurableBundleCartsRestApiDependencyProvider::FACADE_CARTS_REST_API);
    }

    /**
     * @return \Spryker\Zed\ConfigurableBundleCartsRestApi\Dependency\Facade\ConfigurableBundleCartsRestApiToStoreFacadeInterface
     */
    public function getStoreFacade(): ConfigurableBundleCartsRestApiToStoreFacadeInterface
    {
        return $this->getProvidedDependency(ConfigurableBundleCartsRestApiDependencyProvider::FACADE_STORE);
    }

    /**
     * @return \Spryker\Zed\ConfigurableBundleCartsRestApi\Dependency\Service\ConfigurableBundleCartsRestApiToConfigurableBundleServiceInterface
     */
    public function getConfigurableBundleService(): ConfigurableBundleCartsRestApiToConfigurableBundleServiceInterface
    {
        return $this->getProvidedDependency(ConfigurableBundleCartsRestApiDependencyProvider::SERVICE_CONFIGURABLE_BUNDLE);
    }
}
