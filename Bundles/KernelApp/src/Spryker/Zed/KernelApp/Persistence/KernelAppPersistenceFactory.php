<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\KernelApp\Persistence;

use Orm\Zed\KernelApp\Persistence\SpyAppConfigQuery;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;
use Spryker\Zed\KernelApp\Dependency\Service\KernelAppToUtilEncodingServiceInterface;
use Spryker\Zed\KernelApp\KernelAppDependencyProvider;

/**
 * @method \Spryker\Zed\KernelApp\KernelAppConfig getConfig()
 * @method \Spryker\Zed\Locale\Persistence\LocaleRepositoryInterface getRepository()
 * @method \Spryker\Zed\KernelApp\Persistence\KernelAppEntityManagerInterface getEntityManager()
 */
class KernelAppPersistenceFactory extends AbstractPersistenceFactory
{
    /**
     * @return \Orm\Zed\KernelApp\Persistence\SpyAppConfigQuery
     */
    public function createAppConfigPropelQuery(): SpyAppConfigQuery
    {
        return SpyAppConfigQuery::create();
    }

    /**
     * @return \Spryker\Zed\KernelApp\Dependency\Service\KernelAppToUtilEncodingServiceInterface
     */
    public function getUtilEncodingService(): KernelAppToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(KernelAppDependencyProvider::SERVICE_UTIL_ENCODING);
    }
}
