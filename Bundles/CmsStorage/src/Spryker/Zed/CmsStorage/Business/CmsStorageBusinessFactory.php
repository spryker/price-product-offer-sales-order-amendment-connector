<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CmsStorage\Business;

use Spryker\Zed\CmsStorage\Business\Storage\CmsPageStorageWriter;
use Spryker\Zed\CmsStorage\CmsStorageDependencyProvider;
use Spryker\Zed\CmsStorage\Dependency\Facade\CmsStorageToStoreFacadeInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\CmsStorage\CmsStorageConfig getConfig()
 * @method \Spryker\Zed\CmsStorage\Persistence\CmsStorageQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\CmsStorage\Persistence\CmsStorageRepositoryInterface getRepository()
 */
class CmsStorageBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\CmsStorage\Business\Storage\CmsPageStorageWriterInterface
     */
    public function createCmsStorageWriter()
    {
        return new CmsPageStorageWriter(
            $this->getQueryContainer(),
            $this->getCmsFacade(),
            $this->getContentWidgetDataExpanderPlugins(),
            $this->getStoreFacade(),
            $this->getConfig()->isSendingToQueue(),
        );
    }

    /**
     * @return \Spryker\Zed\CmsStorage\Dependency\Facade\CmsStorageToCmsInterface
     */
    protected function getCmsFacade()
    {
        return $this->getProvidedDependency(CmsStorageDependencyProvider::FACADE_CMS);
    }

    /**
     * @return array<\Spryker\Zed\CmsExtension\Dependency\Plugin\CmsPageDataExpanderPluginInterface>
     */
    protected function getContentWidgetDataExpanderPlugins()
    {
        return $this->getProvidedDependency(CmsStorageDependencyProvider::PLUGIN_CONTENT_WIDGET_DATA_EXPANDER);
    }

    /**
     * @return \Spryker\Zed\CmsStorage\Dependency\Facade\CmsStorageToStoreFacadeInterface
     */
    public function getStoreFacade(): CmsStorageToStoreFacadeInterface
    {
        return $this->getProvidedDependency(CmsStorageDependencyProvider::FACADE_STORE);
    }
}
