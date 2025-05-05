<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\SspFileManagement\Business\Facade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CompanyUserTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\DashboardRequestTransfer;
use Generated\Shared\Transfer\DashboardResponseTransfer;
use Generated\Shared\Transfer\FileAttachmentTransfer;
use Spryker\Service\Flysystem\Plugin\FileSystem\FileSystemWriterPlugin;
use Spryker\Service\FlysystemLocalFileSystem\Plugin\Flysystem\LocalFilesystemBuilderPlugin;
use SprykerFeature\Shared\SspFileManagement\Plugin\Permission\ViewCompanyBusinessUnitFilesPermissionPlugin;
use SprykerFeature\Shared\SspFileManagement\Plugin\Permission\ViewCompanyFilesPermissionPlugin;
use SprykerFeature\Shared\SspFileManagement\Plugin\Permission\ViewCompanyUserFilesPermissionPlugin;
use SprykerFeature\Shared\SspFileManagement\SspFileManagementConfig;
use SprykerFeature\Zed\SspFileManagement\Communication\Plugin\SspDashboardManagement\SspFileDashboardDataProviderPlugin;
use SprykerFeatureTest\Zed\SspFileManagement\SspFileManagementBusinessTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerFeatureTest
 * @group Zed
 * @group SspFileManagement
 * @group Business
 * @group Facade
 * @group ProvideDashboardDataFacadeTest
 * Add your own group annotations below this line
 */
class ProvideDashboardDataFacadeTest extends Unit
{
    /**
     * @var \SprykerFeatureTest\Zed\SspFileManagement\SspFileManagementBusinessTester
     */
    protected SspFileManagementBusinessTester $tester;

    /**
     * @var string
     */
    protected const PLUGIN_WRITER = 'PLUGIN_WRITER';

    /**
     * @var string
     */
    public const PLUGIN_COLLECTION_FILESYSTEM_BUILDER = 'filesystem builder plugin collection';

    /**
     * @var string
     */
    public const PLUGINS_PERMISSION = 'PLUGINS_PERMISSION';

    /**
     * @return void
     */
    protected function _before(): void
    {
        $this->tester->setDependency(static::PLUGIN_WRITER, new FileSystemWriterPlugin());
        $this->tester->setDependency(static::PLUGIN_COLLECTION_FILESYSTEM_BUILDER, [
            new LocalFilesystemBuilderPlugin(),
        ]);
        $this->tester->ensureFileAttachmentTablesAreEmpty();
    }

    /**
     * @return void
     */
    public function testProvideDashboardDataWillSucceed(): void
    {
        // Arrange
        $customerTransfer = $this->tester->haveCustomer();
        $companyTransfer = $this->tester->haveCompany();
        $fileTransfer = $this->tester->haveFile();

        $companyUserTransfer = $this->tester->haveCompanyUser(
            [
                CompanyUserTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
                CompanyUserTransfer::CUSTOMER => $customerTransfer,
            ],
        );

        $this->tester->haveFileAttachment([
            FileAttachmentTransfer::FILE => $fileTransfer,
            FileAttachmentTransfer::ENTITY_ID => $companyUserTransfer->getIdCompanyUser(),
            FileAttachmentTransfer::ENTITY_NAME => SspFileManagementConfig::ENTITY_TYPE_COMPANY_USER,
        ]);

        $dashboardRequestTransfer = (new DashboardRequestTransfer())
            ->setCompanyUser($companyUserTransfer);
        $dashboardResponseTransfer = (new DashboardResponseTransfer());

        // Act
        $actualDashboardResponseTransfer = (new SspFileDashboardDataProviderPlugin())->provideDashboardData($dashboardResponseTransfer, $dashboardRequestTransfer);

        // Assert
        $this->assertIsObject($actualDashboardResponseTransfer->getDashboardComponentFiles());
        $this->assertCount(1, $actualDashboardResponseTransfer->getDashboardComponentFiles()->getFileAttachmentFileCollection()->getFileAttachments());
    }

    /**
     * @return void
     */
    public function testProvideDashboardDataWillNotReturnFilesOnCompanySharedFileWithoutRelevantPermission(): void
    {
        // Arrange
        $this->tester->setDependency(static::PLUGINS_PERMISSION, [
            new ViewCompanyFilesPermissionPlugin(),
            new ViewCompanyBusinessUnitFilesPermissionPlugin(),
            new ViewCompanyUserFilesPermissionPlugin(),
        ]);

        $companyTransfer = $this->tester->haveCompany();
        $fileTransfer = $this->tester->haveFile();

        $companyUserTransfer = $this->tester->haveCompanyUser(
            [
                CompanyUserTransfer::FK_COMPANY => $companyTransfer->getIdCompany(),
                CompanyUserTransfer::CUSTOMER => (new CustomerTransfer())->setEmail('test@test.test'),
            ],
        );

        $this->tester->haveFileAttachment([
            FileAttachmentTransfer::FILE => $fileTransfer,
            FileAttachmentTransfer::ENTITY_ID => $companyTransfer->getIdCompany(),
            FileAttachmentTransfer::ENTITY_NAME => SspFileManagementConfig::ENTITY_TYPE_COMPANY,
        ]);

        $dashboardRequestTransfer = (new DashboardRequestTransfer())
            ->setCompanyUser($companyUserTransfer);
        $dashboardResponseTransfer = (new DashboardResponseTransfer());

        // Act
        $actualDashboardResponseTransfer = (new SspFileDashboardDataProviderPlugin())->provideDashboardData($dashboardResponseTransfer, $dashboardRequestTransfer);

        // Assert
        $this->assertIsObject($actualDashboardResponseTransfer->getDashboardComponentFiles());
        $this->assertCount(0, $actualDashboardResponseTransfer->getDashboardComponentFiles()->getFileAttachmentFileCollection()->getFileAttachments());
    }
}
