<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\SelfServicePortal\Communication\Plugin\SspDashboardManagement;

use Generated\Shared\Transfer\DashboardRequestTransfer;
use Generated\Shared\Transfer\DashboardResponseTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use SprykerFeature\Zed\SelfServicePortal\Dependency\Plugin\DashboardDataExpanderPluginInterface;

/**
 * @method \SprykerFeature\Zed\SelfServicePortal\SelfServicePortalConfig getConfig()
 * @method \SprykerFeature\Zed\SelfServicePortal\Business\SelfServicePortalFacadeInterface getFacade()
 * @method \SprykerFeature\Zed\SelfServicePortal\Communication\SelfServicePortalCommunicationFactory getFactory()
 * @method \SprykerFeature\Zed\SelfServicePortal\Business\SelfServicePortalBusinessFactory getBusinessFactory()
 */
class SspAssetDashboardDataExpanderPlugin extends AbstractPlugin implements DashboardDataExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\DashboardResponseTransfer $dashboardResponseTransfer
     * @param \Generated\Shared\Transfer\DashboardRequestTransfer $dashboardRequestTransfer
     *
     * @return \Generated\Shared\Transfer\DashboardResponseTransfer
     */
    public function provideDashboardData(
        DashboardResponseTransfer $dashboardResponseTransfer,
        DashboardRequestTransfer $dashboardRequestTransfer
    ): DashboardResponseTransfer {
        return $this->getBusinessFactory()
            ->createSspAssetDashboardDataExpander()
            ->provideSspAssetDashboardData($dashboardResponseTransfer, $dashboardRequestTransfer);
    }
}
