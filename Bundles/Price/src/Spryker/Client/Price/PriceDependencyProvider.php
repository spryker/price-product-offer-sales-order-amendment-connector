<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Price;

use Spryker\Client\Kernel\AbstractDependencyProvider;
use Spryker\Client\Kernel\Container;
use Spryker\Client\Price\Dependency\Client\PriceToQuoteClientBridge;

/**
 * @method \Spryker\Client\Price\PriceConfig getConfig()
 */
class PriceDependencyProvider extends AbstractDependencyProvider
{
    /**
     * @var string
     */
    public const CLIENT_QUOTE = 'CLIENT_QUOTE';

    /**
     * @var string
     */
    public const PLUGINS_PRICE_MODE_POST_UPDATE = 'PLUGINS_PRICE_MODE_POST_UPDATE';

    /**
     * @var string
     */
    public const PLUGINS_CURRENT_PRICE_MODE_PRE_CHECK = 'PLUGINS_CURRENT_PRICE_MODE_PRE_CHECK';

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    public function provideServiceLayerDependencies(Container $container)
    {
        $container = $this->addQuoteClient($container);
        $container = $this->addPriceModePostUpdatePlugins($container);
        $container = $this->addCurrentPriceModePreCheckPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addQuoteClient(Container $container)
    {
        $container->set(static::CLIENT_QUOTE, function (Container $container) {
            return new PriceToQuoteClientBridge($container->getLocator()->quote()->client());
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addPriceModePostUpdatePlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_PRICE_MODE_POST_UPDATE, function (Container $container) {
            return $this->getPriceModePostUpdatePlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Client\Kernel\Container $container
     *
     * @return \Spryker\Client\Kernel\Container
     */
    protected function addCurrentPriceModePreCheckPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_CURRENT_PRICE_MODE_PRE_CHECK, function () {
            return $this->getCurrentPriceModePreCheckPlugins();
        });

        return $container;
    }

    /**
     * @return list<\Spryker\Client\PriceExtension\Dependency\Plugin\PriceModePostUpdatePluginInterface>
     */
    protected function getPriceModePostUpdatePlugins(): array
    {
        return [];
    }

    /**
     * @return list<\Spryker\Client\PriceExtension\Dependency\Plugin\CurrentPriceModePreCheckPluginInterface>
     */
    protected function getCurrentPriceModePreCheckPlugins(): array
    {
        return [];
    }
}
