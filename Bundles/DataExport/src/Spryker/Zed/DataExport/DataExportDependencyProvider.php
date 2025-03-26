<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DataExport;

use Spryker\Service\DataExport\DataExportServiceInterface;
use Spryker\Zed\DataExport\Dependency\Facade\DataExportToGracefulRunnerFacadeBridge;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

/**
 * @method \Spryker\Zed\DataExport\DataExportConfig getConfig()
 */
class DataExportDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const FACADE_GRACEFUL_RUNNER = 'FACADE_GRACEFUL_RUNNER';

    /**
     * @var string
     */
    public const SERVICE_DATA_EXPORT = 'SERVICE_DATA_EXPORT';

    /**
     * @var string
     */
    public const DATA_ENTITY_EXPORTER_PLUGINS = 'DATA_ENTITY_EXPORTER_PLUGINS';

    /**
     * @var string
     */
    public const DATA_ENTITY_GENERATOR_PLUGINS = 'DATA_ENTITY_GENERATOR_PLUGINS';

    /**
     * @var string
     */
    public const DATA_ENTITY_READER_PLUGINS = 'DATA_ENTITY_READER_PLUGINS';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = parent::provideBusinessLayerDependencies($container);
        $container = $this->addDataExportService($container);
        $container = $this->addDataEntityExporterPlugins($container);
        $container = $this->addDataEntityReaderPlugins($container);
        $container = $this->addDataEntityGeneratorPlugins($container);
        $container = $this->addGracefulRunnerFacade($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = parent::provideCommunicationLayerDependencies($container);
        $container = $this->addDataExportService($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDataExportService(Container $container): Container
    {
        $container->set(static::SERVICE_DATA_EXPORT, function (Container $container): DataExportServiceInterface {
            return $container->getLocator()->dataExport()->service();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDataEntityExporterPlugins(Container $container): Container
    {
        $container->set(static::DATA_ENTITY_EXPORTER_PLUGINS, function (): array {
            return $this->getDataEntityExporterPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\DataExportExtension\Dependency\Plugin\DataEntityExporterPluginInterface>
     */
    protected function getDataEntityExporterPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addGracefulRunnerFacade(Container $container): Container
    {
        $container->set(static::FACADE_GRACEFUL_RUNNER, function (Container $container) {
            return new DataExportToGracefulRunnerFacadeBridge(
                $container->getLocator()->gracefulRunner()->facade(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDataEntityReaderPlugins(Container $container): Container
    {
        $container->set(static::DATA_ENTITY_READER_PLUGINS, function () {
            return $this->getDataEntityReaderPlugins();
        });

        return $container;
    }

    /**
     * @return list<\Spryker\Zed\DataExportExtension\Dependency\Plugin\DataEntityReaderPluginInterface>
     */
    protected function getDataEntityReaderPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDataEntityGeneratorPlugins(Container $container): Container
    {
        $container->set(static::DATA_ENTITY_GENERATOR_PLUGINS, function () {
            return $this->getDataEntityGeneratorPlugins();
        });

        return $container;
    }

    /**
     * @return list<\Spryker\Zed\DataExportExtension\Dependency\Plugin\DataEntityGeneratorPluginInterface>
     */
    protected function getDataEntityGeneratorPlugins(): array
    {
        return [];
    }
}
