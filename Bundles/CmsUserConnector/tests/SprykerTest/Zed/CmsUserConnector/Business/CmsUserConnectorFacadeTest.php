<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CmsUserConnector\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CmsPageAttributesTransfer;
use Generated\Shared\Transfer\CmsPageMetaAttributesTransfer;
use Generated\Shared\Transfer\CmsPageTransfer;
use Generated\Shared\Transfer\CmsVersionTransfer;
use Generated\Shared\Transfer\UserCollectionTransfer;
use Generated\Shared\Transfer\UserTransfer;
use Orm\Zed\Cms\Persistence\SpyCmsVersion;
use Spryker\Zed\Cms\Business\CmsFacade;
use Spryker\Zed\CmsUserConnector\Business\CmsUserConnectorBusinessFactory;
use Spryker\Zed\CmsUserConnector\Business\CmsUserConnectorFacade;
use Spryker\Zed\CmsUserConnector\CmsUserConnectorDependencyProvider;
use Spryker\Zed\CmsUserConnector\Dependency\Facade\CmsUserConnectorToUserBridge;
use Spryker\Zed\CmsUserConnector\Dependency\QueryContainer\CmsUserConnectorToCmsQueryContainer;
use Spryker\Zed\Kernel\Container;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CmsUserConnector
 * @group Business
 * @group Facade
 * @group CmsUserConnectorFacadeTest
 * Add your own group annotations below this line
 */
class CmsUserConnectorFacadeTest extends Unit
{
    /**
     * @var \Spryker\Zed\Cms\Business\CmsFacade
     */
    protected $cmsFacade;

    /**
     * @var \Spryker\Zed\CmsUserConnector\Business\CmsUserConnectorFacade
     */
    protected $cmsUserConnectorFacade;

    /**
     * @return void
     */
    public function testUpdateCmsVersionPluginPersistsUserInformation(): void
    {
        $container = new Container();
        $container[CmsUserConnectorDependencyProvider::FACADE_USER] = function (Container $container) {
            return $this->createUserMockBridgeForUpdating();
        };

        $this->prepareTest($container);

        $fixtures = $this->createCmsPageTransferFixtures();
        $cmsPageTransfer = $this->createCmsPageTransfer($fixtures);
        $idCmsPage = $this->cmsFacade->createPage($cmsPageTransfer);

        $cmsVersionEntity = new SpyCmsVersion();
        $cmsVersionEntity->setFkCmsPage($idCmsPage);
        $cmsVersionEntity->setVersion(1);
        $cmsVersionEntity->setVersionName('v1');
        $cmsVersionEntity->save();

        $cmsVersionTransfer = (new CmsVersionTransfer())->fromArray($cmsVersionEntity->toArray(), true);

        $this->assertNull($cmsVersionEntity->getFkUser());
        $cmsVersionTransfer = $this->cmsUserConnectorFacade->updateCmsVersionUser($cmsVersionTransfer);
        $this->assertNotNull($cmsVersionTransfer->getFkUser());
    }

    /**
     * @return void
     */
    public function testExpandCmsVersionTransferAddsUserInformationToCmsVersionTransfer(): void
    {
        $container = new Container();
        $container[CmsUserConnectorDependencyProvider::FACADE_USER] = function (Container $container) {
            return $this->createUserMockBridgeForExpanding();
        };

        $this->prepareTest($container);
        $cmsVersionTransfer = (new CmsVersionTransfer())->setFkUser(1);

        $this->assertNull($cmsVersionTransfer->getFirstName());
        $this->assertNull($cmsVersionTransfer->getLastName());
        $cmsVersionTransfer = $this->cmsUserConnectorFacade->expandCmsVersionTransferWithUser($cmsVersionTransfer);
        $this->assertNotNull($cmsVersionTransfer->getFirstName());
        $this->assertNotNull($cmsVersionTransfer->getLastName());
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return void
     */
    protected function prepareTest(Container $container): void
    {
        $container[CmsUserConnectorDependencyProvider::QUERY_CONTAINER_CMS] = function (Container $container) {
            return new CmsUserConnectorToCmsQueryContainer($container->getLocator()
                ->cms()
                ->queryContainer());
        };

        $cmsBusinessFactory = new CmsUserConnectorBusinessFactory();
        $cmsBusinessFactory->setContainer($container);

        $this->cmsFacade = new CmsFacade();
        $this->cmsUserConnectorFacade = new CmsUserConnectorFacade();
        $this->cmsUserConnectorFacade->setFactory($cmsBusinessFactory);
    }

    /**
     * @return \Spryker\Zed\CmsUserConnector\Dependency\Facade\CmsUserConnectorToUserBridge|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createUserMockBridgeForUpdating(): CmsUserConnectorToUserBridge
    {
        $userBridgeMock = $this->mockUserBridge();

        return $this->mockUserMethodForUpdateUser($userBridgeMock);
    }

    /**
     * @return \Spryker\Zed\CmsUserConnector\Dependency\Facade\CmsUserConnectorToUserBridge|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createUserMockBridgeForExpanding(): CmsUserConnectorToUserBridge
    {
        $userBridgeMock = $this->mockUserBridge();

        return $this->mockUserMethodForExpandUser($userBridgeMock);
    }

    /**
     * @param \Spryker\Zed\CmsUserConnector\Dependency\Facade\CmsUserConnectorToUserBridge|\PHPUnit\Framework\MockObject\MockObject $userBridgeMock
     *
     * @return \Spryker\Zed\CmsUserConnector\Dependency\Facade\CmsUserConnectorToUserBridge|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function mockUserMethodForUpdateUser($userBridgeMock): CmsUserConnectorToUserBridge
    {
        $userBridgeMock->expects($this->once())
            ->method('hasCurrentUser')
            ->willReturn(true);

        $userBridgeMock->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn($this->createUserTransfer());

        return $userBridgeMock;
    }

    /**
     * @param \Spryker\Zed\CmsUserConnector\Dependency\Facade\CmsUserConnectorToUserBridge|\PHPUnit\Framework\MockObject\MockObject $userBridgeMock
     *
     * @return \Spryker\Zed\CmsUserConnector\Dependency\Facade\CmsUserConnectorToUserBridge|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function mockUserMethodForExpandUser($userBridgeMock): CmsUserConnectorToUserBridge
    {
        $userBridgeMock->expects($this->once())
            ->method('getUserCollection')
            ->willReturn($this->createUserCollectionTransfer());

        return $userBridgeMock;
    }

    /**
     * @return \Generated\Shared\Transfer\UserCollectionTransfer
     */
    protected function createUserCollectionTransfer(): UserCollectionTransfer
    {
        return (new UserCollectionTransfer())->addUser($this->createUserTransfer());
    }

    /**
     * @return \Generated\Shared\Transfer\UserTransfer
     */
    protected function createUserTransfer(): UserTransfer
    {
        $userTransfer = new UserTransfer();
        $userTransfer->setIdUser(1);
        $userTransfer->setFirstName('test');
        $userTransfer->setLastName('test');

        return $userTransfer;
    }

    /**
     * @return \Spryker\Zed\CmsUserConnector\Dependency\Facade\CmsUserConnectorToUserBridge|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function mockUserBridge(): CmsUserConnectorToUserBridge
    {
        return $this->getMockBuilder(CmsUserConnectorToUserBridge::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'hasCurrentUser',
                'getCurrentUser',
                'getUserCollection',
            ])
            ->getMock();
    }

    /**
     * @param array $fixtures
     *
     * @return \Generated\Shared\Transfer\CmsPageTransfer
     */
    protected function createCmsPageTransfer(array $fixtures): CmsPageTransfer
    {
        $cmsPageTransfer = new CmsPageTransfer();
        $cmsPageTransfer->fromArray($fixtures, true);

        return $cmsPageTransfer;
    }

    /**
     * @return array
     */
    protected function createCmsPageTransferFixtures(): array
    {
        $fixtures = [
            CmsPageTransfer::IS_ACTIVE => 1,
            CmsPageTransfer::FK_TEMPLATE => 1,
            CmsPageTransfer::IS_SEARCHABLE => 1,
            CmsPageTransfer::PAGE_ATTRIBUTES => [
                [
                    CmsPageAttributesTransfer::URL => '/en/function-test',
                    CmsPageAttributesTransfer::NAME => 'functional test',
                    CmsPageAttributesTransfer::LOCALE_NAME => 'en_US',
                    CmsPageAttributesTransfer::URL_PREFIX => '/en/',
                    CmsPageAttributesTransfer::FK_LOCALE => 66,
                ],
            ],
            CmsPageTransfer::META_ATTRIBUTES => [
                [
                    CmsPageMetaAttributesTransfer::META_TITLE => 'title english',
                    CmsPageMetaAttributesTransfer::LOCALE_NAME => 'en_US',
                    CmsPageAttributesTransfer::FK_LOCALE => 66,
                ],
            ],
        ];

        return $fixtures;
    }
}
