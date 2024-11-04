<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\DataImport\Model;

use Codeception\Test\Unit;
use Generated\Shared\DataBuilder\DataImporterConfigurationBuilder;
use Generated\Shared\Transfer\DataImporterReportTransfer;
use Spryker\Zed\DataImport\Business\Model\DataImporterCollection;
use Spryker\Zed\DataImport\Business\Model\DataImporterCollectionInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group DataImport
 * @group Model
 * @group DataImporterCollectionTest
 * Add your own group annotations below this line
 * @property \SprykerTest\Zed\DataImport\DataImportBusinessTester $tester
 */
class DataImporterCollectionTest extends Unit
{
    /**
     * @var string
     */
    public const DATA_IMPORTER_FULL = 'full';

    /**
     * @var string
     */
    public const DATA_IMPORTER_TYPE_A = 'data-importer-type-a';

    /**
     * @var string
     */
    public const DATA_IMPORTER_TYPE_B = 'data-importer-type-b';

    /**
     * @var string
     */
    public const DATA_IMPORTER_PLUGIN_TYPE_A = 'data-importer-plugin-type-a';

    /**
     * @var string
     */
    public const DATA_IMPORTER_PLUGIN_TYPE_B = 'data-importer-plugin-type-b';

    /**
     * @var string
     */
    protected const DATA_IMPORTER_IMPORT_GROUP_FULL = 'FULL';

    /**
     * @var string
     */
    protected const DATA_IMPORTER_IMPORT_GROUP_QUEUE_WRITERS = 'QUEUE_WRITERS';

    /**
     * @return void
     */
    public function testAddDataImporter(): void
    {
        $dataImporterCollection = $this->tester->getFactory()->createDataImporterCollection();
        $fluentInterface = $dataImporterCollection->addDataImporter($this->tester->getDataImporterMock(static::DATA_IMPORTER_TYPE_A));

        $this->assertInstanceOf(DataImporterCollectionInterface::class, $fluentInterface);
    }

    /**
     * @return void
     */
    public function testImportReturnsSuccessfulDataImportReportWhenAtLeastOneDataSetWasImported(): void
    {
        $dataImporterReportTransfer = new DataImporterReportTransfer();
        $dataImporterReportTransfer
            ->setIsSuccess(true)
            ->setImportedDataSetCount(1);

        $dataImporterCollection = $this->tester->getFactory()->createDataImporterCollection();
        $dataImporterCollection->addDataImporter($this->tester->getDataImporterMock(static::DATA_IMPORTER_TYPE_A, true, $dataImporterReportTransfer));

        $dataImporterReportTransfer = $dataImporterCollection->import();
        $this->assertTrue($dataImporterReportTransfer->getIsSuccess());
    }

    /**
     * @return void
     */
    public function testImporterPluginCanBeAddedAfterSpecificDataImporter(): void
    {
        //Arrange
        $dataImportCollectionMock = $this->getDataImportCollectionMock();
        $dataImporterA = $this->tester->getDataImporterMock(static::DATA_IMPORTER_TYPE_A);
        $dataImporterB = $this->tester->getDataImporterMock(static::DATA_IMPORTER_TYPE_B);
        $dataImporterPluginA = $this->tester->getDataImporterPluginMock(static::DATA_IMPORTER_PLUGIN_TYPE_A);
        $dataImporterPluginB = $this->tester->getDataImporterPluginMock(static::DATA_IMPORTER_PLUGIN_TYPE_B);

        $dataImportCollectionMock->addDataImporter($dataImporterA);
        $dataImportCollectionMock->addDataImporter($dataImporterB);

        $dataImportCollectionMock->addDataImporterPlugins([
            [$dataImporterPluginA, static::DATA_IMPORTER_TYPE_A],
            $dataImporterPluginB,
        ]);

        $expectedCallSequence = [
            $dataImporterA,
            $dataImporterPluginA,
            $dataImporterB,
            $dataImporterPluginB,
        ];
        $callIndex = 0;

        //Assert
        $dataImportCollectionMock->expects($this->exactly(4))
            ->method('executeDataImporter')
            ->willReturnCallback(function ($dataImporter) use (&$callIndex, $expectedCallSequence) {
                $this->assertSame($expectedCallSequence[$callIndex], $dataImporter);
                $callIndex++;
            });

        //Act
        $dataImportCollectionMock->import();
    }

    /**
     * @return void
     */
    public function testImporterPluginWillAddedAtTheEndIfAddAfterIsNotMatchingToAnyAppliedImporter(): void
    {
        //Arrange
        $dataImportCollectionMock = $this->getDataImportCollectionMock();

        $dataImporterA = $this->tester->getDataImporterMock(static::DATA_IMPORTER_TYPE_A);
        $dataImporterB = $this->tester->getDataImporterMock(static::DATA_IMPORTER_TYPE_B);
        $dataImporterPluginA = $this->tester->getDataImporterPluginMock(static::DATA_IMPORTER_PLUGIN_TYPE_A);

        $dataImportCollectionMock->addDataImporter($dataImporterA);
        $dataImportCollectionMock->addDataImporter($dataImporterB);

        $dataImportCollectionMock->addDataImporterPlugins([
            [$dataImporterPluginA, 'catface'],
        ]);

        $expectedCallSequence = [
            $dataImporterA,
            $dataImporterB,
            $dataImporterPluginA,
        ];
        $callIndex = 0;

        //Assert
        $dataImportCollectionMock->expects($this->exactly(3))
            ->method('executeDataImporter')
            ->willReturnCallback(function ($dataImporter) use (&$callIndex, $expectedCallSequence) {
                $this->assertSame($expectedCallSequence[$callIndex], $dataImporter);
                $callIndex++;
            });

        //Act
        $dataImportCollectionMock->import();
    }

    /**
     * @return void
     */
    public function testAllImportersAreUsedWithFullGroupImport(): void
    {
        //Arrange
        $dataImportCollectionMock = $this->getDataImportCollectionMock();

        $dataImporter = $this->tester->getDataImporterMock(static::DATA_IMPORTER_TYPE_A);
        $dataImporterGroupAware = $this->tester->getDataImporterImportGroupAwareMock(static::DATA_IMPORTER_PLUGIN_TYPE_B, static::DATA_IMPORTER_IMPORT_GROUP_FULL);
        $dataImporterPlugin = $this->tester->getDataImporterPluginMock(static::DATA_IMPORTER_PLUGIN_TYPE_A);

        $dataImportCollectionMock->addDataImporter($dataImporter);
        $dataImportCollectionMock->addDataImporter($dataImporterGroupAware);

        $dataImportCollectionMock->addDataImporterPlugins([
            $dataImporterPlugin,
        ]);

        $expectedCallSequence = [
            $dataImporter,
            $dataImporterGroupAware,
            $dataImporterPlugin,
        ];
        $callIndex = 0;

        //Assert
        $dataImportCollectionMock->expects($this->exactly(3))
            ->method('executeDataImporter')
            ->willReturnCallback(function ($dataImporter) use (&$callIndex, $expectedCallSequence) {
                $this->assertSame($expectedCallSequence[$callIndex], $dataImporter);
                $callIndex++;
            });

        //Act
        $dataImportCollectionMock->import();
    }

    /**
     * @return void
     */
    public function testImportersAreFilteredWithRespectToGroup(): void
    {
        $dataImportCollectionMock = $this->getDataImportCollectionMock();
        $dataImporterConfigurationTransfer = $this->buildDataImporterConfigurationTransfer(static::DATA_IMPORTER_FULL, static::DATA_IMPORTER_IMPORT_GROUP_QUEUE_WRITERS);

        $dataImporter = $this->tester->getDataImporterMock(static::DATA_IMPORTER_TYPE_A);
        $dataImporterGroupAware = $this->tester->getDataImporterImportGroupAwareMock(static::DATA_IMPORTER_PLUGIN_TYPE_B, static::DATA_IMPORTER_IMPORT_GROUP_QUEUE_WRITERS);
        $dataImporterPlugin = $this->tester->getDataImporterPluginMock(static::DATA_IMPORTER_PLUGIN_TYPE_A);

        $dataImportCollectionMock->addDataImporter($dataImporter);
        $dataImportCollectionMock->addDataImporter($dataImporterGroupAware);

        $dataImportCollectionMock->addDataImporterPlugins([
            $dataImporterPlugin,
        ]);

        $dataImportCollectionMock->expects($this->once())->method('executeDataImporter')->with($dataImporterGroupAware);

        $dataImportCollectionMock->import($dataImporterConfigurationTransfer);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\DataImport\Business\Model\DataImporterPluginCollectionInterface|\Spryker\Zed\DataImport\Business\Model\DataImporterCollectionInterface|\Spryker\Zed\DataImport\Business\Model\DataImporter
     */
    protected function getDataImportCollectionMock()
    {
        $dataImportCollectionMockBuilder = $this->getMockBuilder(DataImporterCollection::class)
            ->onlyMethods(['executeDataImporter']);

        $dataImportCollectionMock = $dataImportCollectionMockBuilder->getMock();

        return $dataImportCollectionMock;
    }

    /**
     * @param string $importType
     * @param string $importGroup
     *
     * @return \Generated\Shared\Transfer\DataImporterConfigurationTransfer|\Spryker\Shared\Kernel\Transfer\AbstractTransfer
     */
    protected function buildDataImporterConfigurationTransfer(string $importType, string $importGroup)
    {
        return (new DataImporterConfigurationBuilder([
            'importType' => $importType,
            'importGroup' => $importGroup,
        ]))->build();
    }
}
