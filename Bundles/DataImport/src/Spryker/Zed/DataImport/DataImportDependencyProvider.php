<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\DataImport;

use Propel\Runtime\Propel;
use Spryker\Zed\DataImport\Dependency\Client\DataImportToQueueClientBridge;
use Spryker\Zed\DataImport\Dependency\Facade\DataImportToEventBridge;
use Spryker\Zed\DataImport\Dependency\Facade\DataImportToGracefulRunnerBridge;
use Spryker\Zed\DataImport\Dependency\Facade\DataImportToStoreFacadeBridge;
use Spryker\Zed\DataImport\Dependency\Facade\DataImportToTouchBridge;
use Spryker\Zed\DataImport\Dependency\Propel\DataImportToPropelConnectionBridge;
use Spryker\Zed\DataImport\Dependency\Service\DataImportToFlysystemServiceBridge;
use Spryker\Zed\DataImport\Dependency\Service\DataImportToFlysystemServiceInterface;
use Spryker\Zed\DataImport\Dependency\Service\DataImportToUtilDataReaderServiceBridge;
use Spryker\Zed\DataImport\Dependency\Service\DataImportToUtilEncodingServiceBridge;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

/**
 * @method \Spryker\Zed\DataImport\DataImportConfig getConfig()
 */
class DataImportDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const DATA_IMPORT_STORE_FACADE = 'SPRYKER_STORE_FACADE';

    /**
     * @var string
     */
    public const FACADE_TOUCH = 'touch facade';

    /**
     * @var string
     */
    public const FACADE_EVENT = 'event facade';

    /**
     * @var string
     */
    public const FACADE_GRACEFUL_RUNNER = 'FACADE_GRACEFUL_RUNNER';

    /**
     * @var string
     */
    public const DATA_IMPORTER_PLUGINS = 'IMPORTER_PLUGINS';

    /**
     * @var string
     */
    public const DATA_IMPORT_BEFORE_HOOK_PLUGINS = 'DATA_IMPORT_BEFORE_HOOK_PLUGINS';

    /**
     * @var string
     */
    public const DATA_IMPORT_AFTER_HOOK_PLUGINS = 'DATA_IMPORT_AFTER_HOOK_PLUGINS';

    /**
     * @var string
     */
    public const DATA_IMPORT_DEFAULT_WRITER_PLUGINS = 'DATA_IMPORT_DEFAULT_WRITER_PLUGINS';

    /**
     * @var string
     */
    public const PROPEL_CONNECTION = 'propel connection';

    /**
     * @var string
     */
    public const CLIENT_QUEUE = 'CLIENT_QUEUE';

    /**
     * @var string
     */
    public const SERVICE_UTIL_ENCODING = 'SERVICE_UTIL_ENCODING';

    /**
     * @var string
     */
    public const SERVICE_UTIL_DATA_READER = 'SERVICE_UTIL_DATA_READER';

    /**
     * @var string
     */
    public const SERVICE_FLYSYSTEM = 'SERVICE_FLYSYSTEM';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container)
    {
        $container = $this->addTouchFacade($container);
        $container = $this->addEventFacade($container);
        $container = $this->addGracefulRunnerFacade($container);
        $container = $this->addPropelConnection($container);
        $container = $this->addDataImporterPlugins($container);
        $container = $this->addDataImportBeforeImportHookPlugins($container);
        $container = $this->addDataImportAfterImportHookPlugins($container);
        $container = $this->addDataImportDefaultWriterPlugins($container);
        $container = $this->addQueueClient($container);
        $container = $this->addUtilEncodingService($container);
        $container = $this->addDataImportStoreFacade($container);
        $container = $this->addFlysystemService($container);

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
        $container = $this->addUtilDataReaderService($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addTouchFacade(Container $container)
    {
        $container->set(static::FACADE_TOUCH, function (Container $container) {
            return new DataImportToTouchBridge(
                $container->getLocator()->touch()->facade(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addEventFacade(Container $container)
    {
        $container->set(static::FACADE_EVENT, function (Container $container) {
            return new DataImportToEventBridge(
                $container->getLocator()->event()->facade(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addGracefulRunnerFacade(Container $container): Container
    {
        $container->set(static::FACADE_GRACEFUL_RUNNER, function (Container $container) {
            return new DataImportToGracefulRunnerBridge(
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
    protected function addPropelConnection(Container $container)
    {
        $container->set(static::PROPEL_CONNECTION, function () {
            return new DataImportToPropelConnectionBridge(
                Propel::getConnection(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDataImporterPlugins(Container $container): Container
    {
        $container->set(static::DATA_IMPORTER_PLUGINS, function () {
            return $this->getDataImporterPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\DataImport\Dependency\Plugin\DataImportPluginInterface>
     */
    protected function getDataImporterPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDataImportBeforeImportHookPlugins(Container $container): Container
    {
        $container->set(static::DATA_IMPORT_BEFORE_HOOK_PLUGINS, function () {
            return $this->getDataImportBeforeImportHookPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\DataImport\Business\Model\DataImporterPluginCollectionInterface|\Spryker\Zed\DataImport\Business\Model\DataImporterCollectionInterface>
     */
    protected function getDataImportBeforeImportHookPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDataImportAfterImportHookPlugins(Container $container): Container
    {
        $container->set(static::DATA_IMPORT_AFTER_HOOK_PLUGINS, function () {
            return $this->getDataImportAfterImportHookPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\DataImport\Business\Model\DataImporterPluginCollectionInterface|\Spryker\Zed\DataImport\Business\Model\DataImporterCollectionInterface>
     */
    protected function getDataImportAfterImportHookPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDataImportDefaultWriterPlugins(Container $container): Container
    {
        $container->set(static::DATA_IMPORT_DEFAULT_WRITER_PLUGINS, function () {
            return $this->getDataImportDefaultWriterPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\DataImportExtension\Dependency\Plugin\DataSetWriterPluginInterface>
     */
    protected function getDataImportDefaultWriterPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addQueueClient(Container $container): Container
    {
        $container->set(static::CLIENT_QUEUE, function (Container $container) {
            return new DataImportToQueueClientBridge($container->getLocator()->queue()->client());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addUtilEncodingService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_ENCODING, function (Container $container) {
            return new DataImportToUtilEncodingServiceBridge($container->getLocator()->utilEncoding()->service());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addUtilDataReaderService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_DATA_READER, function (Container $container) {
            return new DataImportToUtilDataReaderServiceBridge($container->getLocator()->utilDataReader()->service());
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addDataImportStoreFacade(Container $container): Container
    {
        $container->set(static::DATA_IMPORT_STORE_FACADE, function (Container $container) {
            return new DataImportToStoreFacadeBridge(
                $container->getLocator()->store()->facade(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addFlysystemService(Container $container): Container
    {
        $container->set(static::SERVICE_FLYSYSTEM, function (Container $container): DataImportToFlysystemServiceInterface {
            return new DataImportToFlysystemServiceBridge(
                $container->getLocator()->flysystem()->service(),
            );
        });

        return $container;
    }
}
