<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\PriceProduct;

use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;
use Spryker\Client\PriceProduct\Dependency\Client\PriceProductToCurrencyClientBridge;
use Spryker\Client\PriceProduct\Dependency\Client\PriceProductToPriceClientBridge;
use Spryker\Client\PriceProduct\Dependency\Client\PriceProductToQuoteClientBridge;

/**
 * @method \Spryker\Client\PriceProduct\PriceProductConfig getConfig()
 */
class PriceProductDependencyProvider extends AbstractDependencyProvider
{
    /**
     * @var string
     */
    public const CLIENT_PRICE = 'CLIENT_PRICE';

    /**
     * @var string
     */
    public const CLIENT_CURRENCY = 'CLIENT_CURRENCY';

    /**
     * @var string
     */
    public const CLIENT_QUOTE = 'CLIENT_QUOTE';

    /**
     * @var string
     */
    public const SERVICE_PRICE_PRODUCT = 'SERVICE_PRICE_PRODUCT';

    /**
     * @var string
     */
    public const PLUGINS_PRICE_PRODUCT_POST_RESOLVE = 'PLUGINS_PRICE_PRODUCT_POST_RESOLVE';

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    public function provideServiceLayerDependencies(Container $container): Container
    {
        $container = $this->addPriceProductClient($container);
        $container = $this->addCurrencyClient($container);
        $container = $this->addQuoteClient($container);
        $container = $this->addPriceProductService($container);
        $container = $this->addPriceProductPostResolvePlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addPriceProductClient(Container $container): Container
    {
        $container->set(static::CLIENT_PRICE, function (Container $container) {
            return new PriceProductToPriceClientBridge($container->getLocator()->price()->client());
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addCurrencyClient(Container $container): Container
    {
        $container->set(static::CLIENT_CURRENCY, function (Container $container) {
            return new PriceProductToCurrencyClientBridge($container->getLocator()->currency()->client());
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addPriceProductService(Container $container): Container
    {
        $container->set(static::SERVICE_PRICE_PRODUCT, function (Container $container) {
            return $container->getLocator()->priceProduct()->service();
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addQuoteClient($container): Container
    {
        $container->set(static::CLIENT_QUOTE, function (Container $container) {
            return new PriceProductToQuoteClientBridge($container->getLocator()->quote()->client());
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addPriceProductPostResolvePlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_PRICE_PRODUCT_POST_RESOLVE, function () {
            return $this->getPriceProductPostResolvePlugins();
        });

        return $container;
    }

    /**
     * @return list<\Spryker\Client\PriceProductExtension\Dependency\Plugin\PriceProductPostResolvePluginInterface>
     */
    protected function getPriceProductPostResolvePlugins(): array
    {
        return [];
    }
}
