<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Merchant\Business\MerchantFacade;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\FilterTransfer;
use Generated\Shared\Transfer\MerchantCollectionTransfer;
use Generated\Shared\Transfer\MerchantCriteriaTransfer;
use Generated\Shared\Transfer\PaginationTransfer;
use Orm\Zed\Merchant\Persistence\Map\SpyMerchantTableMap;
use Spryker\Zed\Merchant\MerchantDependencyProvider;
use Spryker\Zed\MerchantExtension\Dependency\Plugin\MerchantBulkExpanderPluginInterface;
use Spryker\Zed\MerchantExtension\Dependency\Plugin\MerchantExpanderPluginInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Merchant
 * @group Business
 * @group MerchantFacade
 * @group GetMerchantDataTest
 * Add your own group annotations below this line
 */
class GetMerchantDataTest extends Unit
{
    /**
     * @var \SprykerTest\Zed\Merchant\MerchantBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testFindOneMerchant(): void
    {
        // Arrange
        $expectedMerchant = $this->tester->haveMerchant();

        // Act
        $merchantCriteriaTransfer = new MerchantCriteriaTransfer();
        $merchantCriteriaTransfer->setIdMerchant($expectedMerchant->getIdMerchant());

        $actualMerchantById = $this->tester->getFacade()->findOne($merchantCriteriaTransfer);

        $merchantCriteriaTransfer = new MerchantCriteriaTransfer();
        $merchantCriteriaTransfer->setEmail($expectedMerchant->getEmail());

        $actualMerchantByEmail = $this->tester->getFacade()->findOne($merchantCriteriaTransfer);

        $this->assertEquals($expectedMerchant, $actualMerchantById);
        $this->assertEquals($expectedMerchant, $actualMerchantByEmail);
    }

    /**
     * @return void
     */
    public function testFindOneMerchantExecutesExpanderPluginsStack(): void
    {
        // Arrange
        $merchant = $this->tester->haveMerchant();
        $merchantCriteriaTransfer = (new MerchantCriteriaTransfer())
            ->setIdMerchant($merchant->getIdMerchant());
        $merchantCollection = (new MerchantCollectionTransfer())
            ->addMerchants($merchant);

        // Assert
        $merchantExpanderPluginMock = $this
            ->getMockBuilder(MerchantExpanderPluginInterface::class)
            ->getMock();

        $merchantExpanderPluginMock
            ->expects($this->once())
            ->method('expand');

        $this->tester->setDependency(MerchantDependencyProvider::PLUGINS_MERCHANT_EXPANDER, [$merchantExpanderPluginMock]);

        $merchantBulkExpanderPluginMock = $this
            ->getMockBuilder(MerchantBulkExpanderPluginInterface::class)
            ->getMock();

        $merchantBulkExpanderPluginMock
            ->expects($this->once())
            ->method('expand')
            ->willReturn($merchantCollection);

        $this->tester->setDependency(MerchantDependencyProvider::PLUGINS_MERCHANT_BULK_EXPANDER, [$merchantBulkExpanderPluginMock]);

        // Act
        $this->tester->getFacade()
            ->findOne($merchantCriteriaTransfer);
    }

    /**
     * @return void
     */
    public function testFindMerchantByIdWillNotFindMerchant(): void
    {
        // Arrange
        $merchantTransfer = $this->tester->haveMerchant();

        $merchantCriteriaTransfer = new MerchantCriteriaTransfer();
        $merchantCriteriaTransfer->setIdMerchant($merchantTransfer->getIdMerchant() + 1);

        // Act
        $actualMerchant = $this->tester->getFacade()->findOne($merchantCriteriaTransfer);

        // Assert
        $this->assertNull($actualMerchant);
    }

    /**
     * @return void
     */
    public function testFindMerchants(): void
    {
        // Arrange
        $this->tester->truncateMerchantRelations();

        $this->tester->haveMerchant();
        $this->tester->haveMerchant();

        // Act
        $merchantCriteriaTransfer = new MerchantCriteriaTransfer();
        $merchantCollectionWithoutCriteriaTransfer = $this->tester->getFacade()->get($merchantCriteriaTransfer);

        // Arrange
        $paginationTransfer = new PaginationTransfer();
        $paginationTransfer->setPage(1);
        $paginationTransfer->setMaxPerPage(1);
        $merchantCriteriaTransfer->setPagination($paginationTransfer);

        // Act
        $merchantCollectionWithPaginationTransfer = $this->tester->getFacade()->get($merchantCriteriaTransfer);

        // Arrange
        $paginationTransfer = new FilterTransfer();
        $paginationTransfer->setOrderBy(SpyMerchantTableMap::COL_ID_MERCHANT);
        $paginationTransfer->setOrderDirection('DESC');
        $merchantCriteriaTransfer->setFilter($paginationTransfer);

        // Act
        $merchantCollectionWithPaginationTransfer = $this->tester->getFacade()->get($merchantCriteriaTransfer);

        // Arrange
        $merchantCriteriaTransfer->getFilter()->setOrderDirection('ASC');

        // Act
        $merchantCollectionOrderByNameDescTransfer = $this->tester->getFacade()->get($merchantCriteriaTransfer);

        // Assert
        $this->assertCount(2, $merchantCollectionWithoutCriteriaTransfer->getMerchants());
        $this->assertCount(1, $merchantCollectionWithPaginationTransfer->getMerchants());
        $this->assertCount(1, $merchantCollectionOrderByNameDescTransfer->getMerchants());

        $this->assertNotEquals(
            $merchantCollectionOrderByNameDescTransfer->getMerchants()[0]->getIdMerchant(),
            $merchantCollectionWithPaginationTransfer->getMerchants()[0]->getIdMerchant(),
        );
    }

    /**
     * @return void
     */
    public function testGetApplicableMerchantStatusesWillReturnArray(): void
    {
        // Act
        $applicableMerchantStatuses = $this->tester->getFacade()->getApplicableMerchantStatuses($this->tester->createMerchantConfig()->getDefaultMerchantStatus());

        // Assert
        $this->assertTrue(is_array($applicableMerchantStatuses));
        $this->assertNotEmpty($applicableMerchantStatuses);
    }

    /**
     * @return void
     */
    public function testGetApplicableMerchantStatusesWillReturnEmptyArrayOnNotFoundCurrentStatus(): void
    {
        // Act
        $applicableMerchantStatuses = $this->tester->getFacade()->getApplicableMerchantStatuses('random-status');

        // Assert
        $this->assertTrue(is_array($applicableMerchantStatuses));
        $this->assertEmpty($applicableMerchantStatuses);
    }

    /**
     * @return void
     */
    public function testReturnsMerchantsPaginatedByLimitAndOffset(): void
    {
        // Arrange
        $this->tester->truncateMerchantRelations();
        $this->tester->haveMerchant();
        $merchantTransfer = $this->tester->haveMerchant();

        $paginationTransfer = (new PaginationTransfer())
            ->setLimit(1)
            ->setOffset(1);

        $filterTransfer = new FilterTransfer();
        $filterTransfer->setOrderBy(SpyMerchantTableMap::COL_ID_MERCHANT);
        $filterTransfer->setOrderDirection('ASC');

        $merchantCriteriaTransfer = (new MerchantCriteriaTransfer())
            ->setPagination($paginationTransfer)
            ->setFilter($filterTransfer);

        // Act
        $merchantCollectionTransfer = $this->tester->getFacade()->get($merchantCriteriaTransfer);

        // Assert
        $this->assertCount(1, $merchantCollectionTransfer->getMerchants());
        $this->assertSame($merchantTransfer->getIdMerchant(), $merchantCollectionTransfer->getMerchants()->offsetGet(0)->getIdMerchant());
    }
}
