<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\DataImport\Business\Model\DataSet;

use Codeception\Test\Unit;
use Spryker\Zed\DataImport\Business\Exception\DataSetBrokerTransactionFailedException;
use Spryker\Zed\DataImport\Business\Exception\TransactionException;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\DataImportDependencyProvider;
use Spryker\Zed\DataImport\Dependency\Propel\DataImportToPropelConnectionInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group DataImport
 * @group Business
 * @group Model
 * @group DataSet
 * @group DataSetStepBrokerTransactionAwareTest
 * Add your own group annotations below this line
 * @property \SprykerTest\Zed\DataImport\DataImportBusinessTester $tester
 */
class DataSetStepBrokerTransactionAwareTest extends Unit
{
    /**
     * @return void
     */
    public function testExecuteOpensTransactionOnFirstCall(): void
    {
        $propelConnectionMock = $this->getPropelConnectionMock(1, 1, false, true);
        $this->tester->setDependency(DataImportDependencyProvider::PROPEL_CONNECTION, $propelConnectionMock);

        $dataSet = $this->tester->getFactory()->createDataSet();
        $dataSetStepBrokerTransactionAware = $this->tester->getFactory()->createTransactionAwareDataSetStepBroker();
        $dataSetStepBrokerTransactionAware->execute($dataSet);
    }

    /**
     * @return void
     */
    public function testTransactionOnlyOpenedOnceForConfiguredBulkSize(): void
    {
        $propelConnectionMock = $this->getPropelConnectionMock(1, 1, false, true, true);
        $this->tester->setDependency(DataImportDependencyProvider::PROPEL_CONNECTION, $propelConnectionMock);

        $dataSet = $this->tester->getFactory()->createDataSet();
        $dataSetStepBrokerTransactionAware = $this->tester->getFactory()->createTransactionAwareDataSetStepBroker(2);
        $dataSetStepBrokerTransactionAware->execute($dataSet);
        $dataSetStepBrokerTransactionAware->execute($dataSet);
    }

    /**
     * @return void
     */
    public function testTransactionIsOpenedForeachConfiguredBulkSize(): void
    {
        $propelConnectionMock = $this->getPropelConnectionMock(2, 2, false, true, true, false, true, true);
        $this->tester->setDependency(DataImportDependencyProvider::PROPEL_CONNECTION, $propelConnectionMock);

        $dataSet = $this->tester->getFactory()->createDataSet();
        $dataSetStepBrokerTransactionAware = $this->tester->getFactory()->createTransactionAwareDataSetStepBroker(2);
        $dataSetStepBrokerTransactionAware->execute($dataSet);
        $dataSetStepBrokerTransactionAware->execute($dataSet);

        $dataSetStepBrokerTransactionAware->execute($dataSet);
    }

    /**
     * @return void
     */
    public function testTransactionNotOpenedWhenAlreadyInTransaction(): void
    {
        $propelConnectionMock = $this->getPropelConnectionMock(0, 1, true, true);
        $this->tester->setDependency(DataImportDependencyProvider::PROPEL_CONNECTION, $propelConnectionMock);

        $dataSet = $this->tester->getFactory()->createDataSet();
        $dataSetStepBrokerTransactionAware = $this->tester->getFactory()->createTransactionAwareDataSetStepBroker();
        $dataSetStepBrokerTransactionAware->execute($dataSet);
    }

    /**
     * @return void
     */
    public function testTransactionIsClosedForeachConfiguredBulkSize(): void
    {
        $propelConnectionMock = $this->getPropelConnectionMock(1, 1, false, true);
        $this->tester->setDependency(DataImportDependencyProvider::PROPEL_CONNECTION, $propelConnectionMock);

        $dataSet = $this->tester->getFactory()->createDataSet();
        $dataSetStepBrokerTransactionAware = $this->tester->getFactory()->createTransactionAwareDataSetStepBroker();
        $dataSetStepBrokerTransactionAware->execute($dataSet);
    }

    /**
     * @return void
     */
    public function testTransactionIsOnlyClosedOnceForConfiguredBulkSize(): void
    {
        $propelConnectionMock = $this->getPropelConnectionMock(1, 1, false, true, true);
        $this->tester->setDependency(DataImportDependencyProvider::PROPEL_CONNECTION, $propelConnectionMock);

        $dataSet = $this->tester->getFactory()->createDataSet();
        $dataSetStepBrokerTransactionAware = $this->tester->getFactory()->createTransactionAwareDataSetStepBroker(2);
        $dataSetStepBrokerTransactionAware->execute($dataSet);
        $dataSetStepBrokerTransactionAware->execute($dataSet);
    }

    /**
     * @return void
     */
    public function testTransactionIsAlwaysClosedWhenThereIsAnOpenedOne(): void
    {
        $propelConnectionMock = $this->getPropelConnectionMock(2, 2, false, true, true, false, true, true);
        $this->tester->setDependency(DataImportDependencyProvider::PROPEL_CONNECTION, $propelConnectionMock);

        $dataSet = $this->tester->getFactory()->createDataSet();
        $dataSetStepBrokerTransactionAware = $this->tester->getFactory()->createTransactionAwareDataSetStepBroker(2);

        $dataSetStepBrokerTransactionAware->execute($dataSet);
        $dataSetStepBrokerTransactionAware->execute($dataSet);
        $dataSetStepBrokerTransactionAware->execute($dataSet);
    }

    /**
     * @return void
     */
    public function testThrowsExceptionIfNoOpenTransactionGiven(): void
    {
        //Arrange
        $propelConnectionMock = $this->getPropelConnectionMock(1, 0, false, false, false);
        $this->tester->setDependency(DataImportDependencyProvider::PROPEL_CONNECTION, $propelConnectionMock);

        $dataSet = $this->tester->getFactory()->createDataSet();
        $dataSetStepBrokerTransactionAware = $this->tester->getFactory()->createTransactionAwareDataSetStepBroker();

        //Assert
        $this->expectException(TransactionException::class);

        //Act
        $dataSetStepBrokerTransactionAware->execute($dataSet);
    }

    /**
     * @return void
     */
    public function testTransactionRollBackOnWriterException(): void
    {
        //Arrange
        $this->tester->setDependency(DataImportDependencyProvider::PROPEL_CONNECTION, $this->createPropelConnectionMockWithExpectedRollBack());

        $dataImportStepMock = $this->createDataImportStepMockWithExpectedExceptionOnExecute();
        $dataSetStepBrokerTransactionAware = $this->tester->getFactory()->createTransactionAwareDataSetStepBroker();
        $dataSetStepBrokerTransactionAware->addStep($dataImportStepMock);

        //Act
        $dataSetStepBrokerTransactionAware->execute($this->tester->getFactory()->createDataSet());
    }

    /**
     * @param int $beginTransactionCalledCount
     * @param int $endTransactionCalledCount
     * @param mixed $isInTransaction
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\DataImport\Dependency\Propel\DataImportToPropelConnectionInterface
     */
    private function getPropelConnectionMock(
        int $beginTransactionCalledCount,
        int $endTransactionCalledCount,
        ...$isInTransaction
    ): DataImportToPropelConnectionInterface {
        $mockBuilder = $this->getMockBuilder(DataImportToPropelConnectionInterface::class)
            ->onlyMethods(['inTransaction', 'beginTransaction', 'endTransaction', 'rollBack']);

        $propelConnectionMock = $mockBuilder->getMock();

        $propelConnectionMock->method('inTransaction')->will($this->onConsecutiveCalls(...$isInTransaction));
        $propelConnectionMock->expects($this->exactly($beginTransactionCalledCount))->method('beginTransaction');
        $propelConnectionMock->expects($this->exactly($endTransactionCalledCount))->method('endTransaction');

        return $propelConnectionMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface
     */
    protected function createDataImportStepMockWithExpectedExceptionOnExecute(): DataImportStepInterface
    {
        $dataImportStepMockBuilder = $this->getMockBuilder(DataImportStepInterface::class)
            ->onlyMethods(['execute']);
        $dataImportStepMock = $dataImportStepMockBuilder->getMock();
        $dataImportStepMock->expects($this->once())->method('execute')->willThrowException(new DataSetBrokerTransactionFailedException(10));
        $this->expectException(DataSetBrokerTransactionFailedException::class);

        return $dataImportStepMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\DataImport\Dependency\Propel\DataImportToPropelConnectionInterface
     */
    protected function createPropelConnectionMockWithExpectedRollBack(): DataImportToPropelConnectionInterface
    {
        $propelConnectionMock = $this->getPropelConnectionMock(1, 0, false);
        $propelConnectionMock->expects($this->exactly(1))->method('rollBack');

        return $propelConnectionMock;
    }
}
