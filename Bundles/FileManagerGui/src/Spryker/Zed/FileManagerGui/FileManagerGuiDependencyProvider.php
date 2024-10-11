<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\FileManagerGui;

use Orm\Zed\FileManager\Persistence\SpyFileInfoQuery;
use Orm\Zed\FileManager\Persistence\SpyFileQuery;
use Orm\Zed\FileManager\Persistence\SpyMimeTypeQuery;
use Spryker\Zed\FileManagerGui\Dependency\Facade\FileManagerGuiToFileManagerFacadeBridge;
use Spryker\Zed\FileManagerGui\Dependency\Facade\FileManagerGuiToLocaleFacadeBridge;
use Spryker\Zed\FileManagerGui\Dependency\Service\FileManagerGuiToUtilEncodingServiceBridge;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

/**
 * @method \Spryker\Zed\FileManagerGui\FileManagerGuiConfig getConfig()
 */
class FileManagerGuiDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const FACADE_FILE_MANAGER = 'FACADE_FILE_MANAGER';

    /**
     * @var string
     */
    public const FACADE_LOCALE = 'FACADE_LOCALE';

    /**
     * @var string
     */
    public const PROPEL_QUERY_FILE = 'PROPEL_QUERY_FILE';

    /**
     * @var string
     */
    public const PROPEL_QUERY_FILE_INFO = 'PROPEL_QUERY_FILE_INFO';

    /**
     * @var string
     */
    public const PROPEL_QUERY_MIME_TYPE = 'PROPEL_QUERY_MIME_TYPE';

    /**
     * @var string
     */
    public const SERVICE_UTIL_ENCODING = 'SERVICE_UTIL_ENCODING';

    /**
     * @var string
     */
    public const PLUGINS_FILE_INFO_VIEW_TABLE_ACTIONS_EXPANDER = 'PLUGINS_FILE_INFO_VIEW_TABLE_ACTIONS_EXPANDER';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container)
    {
        $container = parent::provideCommunicationLayerDependencies($container);

        $container = $this->addFileManagerFacade($container);
        $container = $this->addLocaleFacade($container);
        $container = $this->addQueries($container);
        $container = $this->addUtilEncodingService($container);
        $container = $this->addTableActionsExpanderPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addFileManagerFacade(Container $container)
    {
        $container->set(static::FACADE_FILE_MANAGER, function (Container $container) {
            return new FileManagerGuiToFileManagerFacadeBridge(
                $container->getLocator()->fileManager()->facade(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addLocaleFacade(Container $container)
    {
        $container->set(static::FACADE_LOCALE, function (Container $container) {
            return new FileManagerGuiToLocaleFacadeBridge(
                $container->getLocator()->locale()->facade(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addQueries(Container $container)
    {
        $container = $this->addFileQuery($container);
        $container = $this->addFileInfoQuery($container);
        $container = $this->addMimeTypeQuery($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addFileQuery(Container $container): Container
    {
        $container->set(static::PROPEL_QUERY_FILE, $container->factory(function () {
            return SpyFileQuery::create();
        }));

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addFileInfoQuery(Container $container): Container
    {
        $container->set(static::PROPEL_QUERY_FILE_INFO, $container->factory(function () {
            return SpyFileInfoQuery::create();
        }));

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMimeTypeQuery(Container $container): Container
    {
        $container->set(static::PROPEL_QUERY_MIME_TYPE, $container->factory(function () {
            return SpyMimeTypeQuery::create();
        }));

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
            return new FileManagerGuiToUtilEncodingServiceBridge(
                $container->getLocator()->utilEncoding()->service(),
            );
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addTableActionsExpanderPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_FILE_INFO_VIEW_TABLE_ACTIONS_EXPANDER, function () {
            return $this->getFileInfoViewTableActionsExpanderPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\FileManagerGuiExtension\Dependency\Plugin\FileInfoViewTableActionsExpanderPluginInterface>
     */
    protected function getFileInfoViewTableActionsExpanderPlugins(): array
    {
        return [];
    }
}
