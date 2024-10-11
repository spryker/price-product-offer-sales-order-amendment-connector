<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantGui;

use Orm\Zed\Merchant\Persistence\SpyMerchantQuery;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Communication\Form\FormTypeInterface;
use Spryker\Zed\Kernel\Container;
use Spryker\Zed\MerchantGui\Communication\Exception\MissingStoreRelationFormTypePluginException;
use Spryker\Zed\MerchantGui\Dependency\Facade\MerchantGuiToLocaleFacadeBridge;
use Spryker\Zed\MerchantGui\Dependency\Facade\MerchantGuiToMerchantFacadeBridge;
use Spryker\Zed\MerchantGui\Dependency\Facade\MerchantGuiToUrlFacadeBridge;

/**
 * @method \Spryker\Zed\MerchantGui\MerchantGuiConfig getConfig()
 */
class MerchantGuiDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const FACADE_MERCHANT = 'FACADE_MERCHANT';

    /**
     * @var string
     */
    public const PROPEL_MERCHANT_QUERY = 'PROPEL_MERCHANT_QUERY';

    /**
     * @var string
     */
    public const FACADE_URL = 'FACADE_URL';

    /**
     * @var string
     */
    public const FACADE_LOCALE = 'FACADE_LOCALE';

    /**
     * @var string
     */
    public const PLUGINS_MERCHANT_FORM_EXPANDER = 'PLUGINS_MERCHANT_FORM_EXPANDER';

    /**
     * @var string
     */
    public const PLUGINS_MERCHANT_TABLE_DATA_EXPANDER = 'PLUGINS_MERCHANT_TABLE_DATA_EXPANDER';

    /**
     * @var string
     */
    public const PLUGINS_MERCHANT_TABLE_ACTION_EXPANDER = 'PLUGINS_MERCHANT_TABLE_ACTION_EXPANDER';

    /**
     * @var string
     */
    public const PLUGINS_MERCHANT_TABLE_HEADER_EXPANDER = 'PLUGINS_MERCHANT_TABLE_HEADER_EXPANDER';

    /**
     * @var string
     */
    public const PLUGINS_MERCHANT_TABLE_CONFIG_EXPANDER = 'PLUGINS_MERCHANT_TABLE_CONFIG_EXPANDER';

    /**
     * @var string
     */
    public const PLUGINS_MERCHANT_FORM_TABS_EXPANDER = 'PLUGINS_MERCHANT_FORM_TABS_EXPANDER';

    /**
     * @var string
     */
    public const PLUGINS_MERCHANT_UPDATE_FORM_VIEW_EXPANDER = 'PLUGINS_MERCHANT_UPDATE_FORM_VIEW_EXPANDER';

    /**
     * @var string
     */
    public const PLUGINS_MERCHANT_VIEW_FORM_VIEW_EXPANDER = 'PLUGINS_MERCHANT_VIEW_FORM_VIEW_EXPANDER';

    /**
     * @var string
     */
    public const PLUGIN_STORE_RELATION_FORM_TYPE = 'PLUGIN_STORE_RELATION_FORM_TYPE';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);

        $container = $this->addMerchantFacade($container);
        $container = $this->addPropelMerchantQuery($container);
        $container = $this->addMerchantFormExpanderPlugins($container);
        $container = $this->addMerchantTableActionExpanderPlugins($container);
        $container = $this->addMerchantTableDataExpanderPlugins($container);
        $container = $this->addMerchantTableHeaderExpanderPlugins($container);
        $container = $this->addMerchantTableConfigExpanderPlugins($container);
        $container = $this->addMerchantFormTabsExpanderPlugins($container);
        $container = $this->addUrlFacade($container);
        $container = $this->addLocaleFacade($container);
        $container = $this->addMerchantUpdateFormViewExpanderPlugins($container);
        $container = $this->addMerchantViewFormViewExpanderPlugins($container);
        $container = $this->addStoreRelationFormTypePlugin($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMerchantFacade(Container $container): Container
    {
        $container->set(static::FACADE_MERCHANT, function (Container $container) {
            return new MerchantGuiToMerchantFacadeBridge($container->getLocator()->merchant()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addPropelMerchantQuery(Container $container): Container
    {
        $container->set(static::PROPEL_MERCHANT_QUERY, $container->factory(function () {
            return SpyMerchantQuery::create();
        }));

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMerchantFormExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_MERCHANT_FORM_EXPANDER, function () {
            return $this->getMerchantFormExpanderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMerchantTableActionExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_MERCHANT_TABLE_ACTION_EXPANDER, function () {
            return $this->getMerchantTableActionExpanderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMerchantTableDataExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_MERCHANT_TABLE_DATA_EXPANDER, function () {
            return $this->getMerchantTableDataExpanderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMerchantTableHeaderExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_MERCHANT_TABLE_HEADER_EXPANDER, function () {
            return $this->getMerchantTableHeaderExpanderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMerchantTableConfigExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_MERCHANT_TABLE_CONFIG_EXPANDER, function () {
            return $this->getMerchantTableConfigExpanderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMerchantFormTabsExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_MERCHANT_FORM_TABS_EXPANDER, function () {
            return $this->getMerchantFormTabsExpanderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMerchantUpdateFormViewExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_MERCHANT_UPDATE_FORM_VIEW_EXPANDER, function () {
            return $this->getMerchantUpdateFormViewExpanderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMerchantViewFormViewExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_MERCHANT_VIEW_FORM_VIEW_EXPANDER, function () {
            return $this->getMerchantViewFormViewExpanderPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addUrlFacade(Container $container): Container
    {
        $container->set(static::FACADE_URL, function (Container $container) {
            return new MerchantGuiToUrlFacadeBridge($container->getLocator()->url()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addLocaleFacade(Container $container): Container
    {
        $container->set(static::FACADE_LOCALE, function (Container $container) {
            return new MerchantGuiToLocaleFacadeBridge($container->getLocator()->locale()->facade());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addStoreRelationFormTypePlugin(Container $container): Container
    {
        $container->set(static::PLUGIN_STORE_RELATION_FORM_TYPE, function () {
            return $this->getStoreRelationFormTypePlugin();
        });

        return $container;
    }

    /**
     * @throws \Spryker\Zed\MerchantGui\Communication\Exception\MissingStoreRelationFormTypePluginException
     *
     * @return \Spryker\Zed\Kernel\Communication\Form\FormTypeInterface
     */
    protected function getStoreRelationFormTypePlugin(): FormTypeInterface
    {
        throw new MissingStoreRelationFormTypePluginException(
            sprintf(
                'Missing instance of %s! You need to configure StoreRelationFormType ' .
                'in your own MerchantGuiDependencyProvider::getStoreRelationFormTypePlugin() ' .
                'to be able to manage merchants.',
                FormTypeInterface::class,
            ),
        );
    }

    /**
     * @return array<\Spryker\Zed\MerchantGuiExtension\Dependency\Plugin\MerchantFormExpanderPluginInterface>
     */
    protected function getMerchantFormExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Zed\MerchantGuiExtension\Dependency\Plugin\MerchantTableDataExpanderPluginInterface>
     */
    protected function getMerchantTableDataExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Zed\MerchantGuiExtension\Dependency\Plugin\MerchantTableDataExpanderPluginInterface>
     */
    protected function getMerchantTableActionExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Zed\MerchantGuiExtension\Dependency\Plugin\MerchantTableHeaderExpanderPluginInterface>
     */
    protected function getMerchantTableHeaderExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Zed\MerchantGuiExtension\Dependency\Plugin\MerchantTableConfigExpanderPluginInterface>
     */
    protected function getMerchantTableConfigExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Zed\MerchantGuiExtension\Dependency\Plugin\MerchantFormTabExpanderPluginInterface>
     */
    protected function getMerchantFormTabsExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Zed\MerchantGuiExtension\Dependency\Plugin\MerchantUpdateFormViewExpanderPluginInterface>
     */
    protected function getMerchantUpdateFormViewExpanderPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Zed\MerchantGuiExtension\Dependency\Plugin\MerchantUpdateFormViewExpanderPluginInterface>
     */
    protected function getMerchantViewFormViewExpanderPlugins(): array
    {
        return [];
    }
}
