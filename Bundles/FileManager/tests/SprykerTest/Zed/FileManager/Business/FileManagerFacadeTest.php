<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\FileManager\Business;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\FileDirectoryTransfer;
use Generated\Shared\Transfer\FileDirectoryTreeNodeTransfer;
use Generated\Shared\Transfer\FileDirectoryTreeTransfer;
use Generated\Shared\Transfer\FileInfoTransfer;
use Generated\Shared\Transfer\FileManagerDataCollectionTransfer;
use Generated\Shared\Transfer\FileManagerDataTransfer;
use Generated\Shared\Transfer\FileTransfer;
use Generated\Shared\Transfer\MimeTypeCollectionTransfer;
use Generated\Shared\Transfer\MimeTypeResponseTransfer;
use Generated\Shared\Transfer\MimeTypeTransfer;
use Orm\Zed\FileManager\Persistence\SpyFile;
use Orm\Zed\FileManager\Persistence\SpyFileQuery;
use Orm\Zed\FileManager\Persistence\SpyMimeType;
use Orm\Zed\FileManager\Persistence\SpyMimeTypeQuery;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Service\FileSystem\FileSystemDependencyProvider;
use Spryker\Service\FileSystem\FileSystemService;
use Spryker\Service\FileSystem\FileSystemServiceFactory;
use Spryker\Service\Flysystem\FlysystemDependencyProvider;
use Spryker\Service\Flysystem\FlysystemService;
use Spryker\Service\Flysystem\FlysystemServiceFactory;
use Spryker\Service\Flysystem\Plugin\FileSystem\FileSystemReaderPlugin;
use Spryker\Service\Flysystem\Plugin\FileSystem\FileSystemStreamPlugin;
use Spryker\Service\Flysystem\Plugin\FileSystem\FileSystemWriterPlugin;
use Spryker\Service\FlysystemLocalFileSystem\Plugin\Flysystem\LocalFilesystemBuilderPlugin;
use Spryker\Service\Kernel\Container as ServiceContainer;
use Spryker\Zed\FileManager\Business\FileManagerBusinessFactory;
use Spryker\Zed\FileManager\Business\FileManagerFacade;
use Spryker\Zed\FileManager\Dependency\Service\FileManagerToFileSystemServiceBridge;
use Spryker\Zed\FileManager\FileManagerDependencyProvider;
use Spryker\Zed\FileManagerExtension\Dependency\Plugin\FileManagerDataCollectionExpanderPluginInterface;
use Spryker\Zed\FileManagerExtension\Dependency\Plugin\FileManagerDataCollectionExpanderPreSavePluginInterface;
use Spryker\Zed\Kernel\Container;
use SprykerTest\Zed\FileManager\Stub\FileManagerConfigStub;
use SprykerTest\Zed\FileManager\Stub\FileSystemConfigStub;
use SprykerTest\Zed\FileManager\Stub\FlysystemConfigStub;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group FileManager
 * @group Business
 * @group Facade
 * @group FileManagerFacadeTest
 * Add your own group annotations below this line
 */
class FileManagerFacadeTest extends Unit
{
    /**
     * @var \SprykerTest\Zed\FileManager\FileManagerBusinessTester
     */
    protected $tester;

    /**
     * @var \Spryker\Zed\FileManager\Business\FileManagerFacade
     */
    protected $facade;

    /**
     * @var string
     */
    protected $testDataFileSystemRootDirectory;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        file_put_contents($this->tester->getDocumentFullFileName('customer_v1.txt'), 'first version of the file');
        file_put_contents($this->tester->getDocumentFullFileName('customer_v2.txt'), 'second version of the file');

        $serviceContainer = new ServiceContainer();
        $serviceContainer = $this->setupContainerAndFlysystemService($serviceContainer);

        $config = new FileSystemConfigStub();
        $factory = new FileSystemServiceFactory();
        $factory->setConfig($config);
        $factory->setContainer($serviceContainer);

        $fileSystemService = new FileSystemService();
        $fileSystemService->setFactory($factory);
        $container = new Container();

        $container[FileManagerDependencyProvider::SERVICE_FILE_SYSTEM] = function (Container $container) use ($fileSystemService) {
            return new FileManagerToFileSystemServiceBridge($fileSystemService);
        };

        $container[FileManagerDependencyProvider::PLUGINS_FILE_MANAGER_DATA_COLLECTION_EXPANDER_PRE_SAVE] = function (Container $container) {
            return [$this->getFileManagerDataCollectionExpanderPreSavePluginMock()];
        };

        $container[FileManagerDependencyProvider::PLUGINS_FILE_MANAGER_DATA_COLLECTION_EXPANDER] = function (Container $container) {
            return [$this->getFileManagerDataCollectionExpanderPluginMock()];
        };

        $config = new FileManagerConfigStub();
        $factory = new FileManagerBusinessFactory();
        $factory->setContainer($container);
        $factory->setConfig($config);

        $this->facade = new FileManagerFacade();
        $this->facade->setFactory($factory);

        $this->tester->insertDbRecords();
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->tester->resetDb();
        $this->tester->clearFiles();
    }

    /**
     * @param \Spryker\Service\Kernel\Container $container
     *
     * @return \Spryker\Service\Kernel\Container
     */
    protected function setupContainerAndFlysystemService(ServiceContainer $container): ServiceContainer
    {
        $flysystemContainer = new ServiceContainer();
        $flysystemContainer[FlysystemDependencyProvider::PLUGIN_COLLECTION_FILESYSTEM_BUILDER] = function (ServiceContainer $flysystemContainer) {
            return [
                new LocalFilesystemBuilderPlugin(),
            ];
        };

        $flysystemContainer[FlysystemDependencyProvider::PLUGIN_COLLECTION_FLYSYSTEM] = function (ServiceContainer $flysystemContainer) {
            return [];
        };

        $flysystemConfig = new FlysystemConfigStub();

        $flysystemFactory = new FlysystemServiceFactory();
        $flysystemFactory->setContainer($flysystemContainer);
        $flysystemFactory->setConfig($flysystemConfig);

        $flysystemService = new FlysystemService();
        $flysystemService->setFactory($flysystemFactory);

        $fileSystemReaderPlugin = new FileSystemReaderPlugin();
        $fileSystemReaderPlugin->setService($flysystemService);

        $fileSystemWriterPlugin = new FileSystemWriterPlugin();
        $fileSystemWriterPlugin->setService($flysystemService);

        $fileSystemStreamPlugin = new FileSystemStreamPlugin();
        $fileSystemStreamPlugin->setService($flysystemService);

        $container[FileSystemDependencyProvider::PLUGIN_READER] = function (ServiceContainer $container) use ($fileSystemReaderPlugin) {
            return $fileSystemReaderPlugin;
        };

        $container[FileSystemDependencyProvider::PLUGIN_WRITER] = function (ServiceContainer $container) use ($fileSystemWriterPlugin) {
            return $fileSystemWriterPlugin;
        };

        $container[FileSystemDependencyProvider::PLUGIN_STREAM] = function (ServiceContainer $container) use ($fileSystemStreamPlugin) {
            return $fileSystemStreamPlugin;
        };

        return $container;
    }

    /**
     * @return void
     */
    public function testReadsLatestVersionOfFile(): void
    {
        $fileManagerDataTransfer = $this->facade->findFileByIdFile($this->tester->getIdFile());
        $this->assertSame('second version of the file', $fileManagerDataTransfer->getContent());
        $this->assertSame('customer.txt', $fileManagerDataTransfer->getFile()->getFileName());
        $this->assertSame($this->tester->getIdFile(), $fileManagerDataTransfer->getFile()->getIdFile());
        $this->assertSame('customer_v2.txt', $fileManagerDataTransfer->getFileInfo()->getStorageFileName());
        $this->assertSame('txt', $fileManagerDataTransfer->getFileInfo()->getExtension());
        $this->assertSame(2, $fileManagerDataTransfer->getFileInfo()->getVersion());
        $this->assertSame('v. 2', $fileManagerDataTransfer->getFileInfo()->getVersionName());
        $this->assertSame(10, $fileManagerDataTransfer->getFileInfo()->getSize());
    }

    /**
     * @return void
     */
    public function testDelete(): void
    {
        $this->assertTrue($this->facade->deleteFile($this->tester->getIdFile()));
    }

    /**
     * @return void
     */
    public function testDeleteFileInfo(): void
    {
        $this->assertTrue($this->facade->deleteFileInfo($this->tester->getIdFirstFileInfo()));
    }

    /**
     * @return void
     */
    public function testSave(): void
    {
        // Arrange
        $fileManagerDataTransfer = $this->createFileManagerDataTransfer();

        // Act
        $fileManagerDataTransfer = $this->facade->saveFile($fileManagerDataTransfer);

        // Assert
        $file = SpyFileQuery::create()->findOneByFileName('newcustomer.txt');

        $this->assertEquals($this->tester->getIdFile() + 1, $fileManagerDataTransfer->getFile()->getIdFile());
        $this->assertInstanceOf(SpyFile::class, $file);
    }

    /**
     * @return void
     */
    public function testRollback(): void
    {
        $this->facade->rollbackFile($this->tester->getIdFirstFileInfo());
        $fileManagerDataTransfer = $this->facade->readLatestFileVersion($this->tester->getIdFile());
        $this->assertSame('first version of the file', $fileManagerDataTransfer->getContent());
        $this->assertSame('customer_v1.txt', $fileManagerDataTransfer->getFileInfo()->getStorageFileName());
        $this->assertSame('txt', $fileManagerDataTransfer->getFileInfo()->getExtension());
        $this->assertSame(3, $fileManagerDataTransfer->getFileInfo()->getVersion());
        $this->assertSame('v.3', $fileManagerDataTransfer->getFileInfo()->getVersionName());
        $this->assertSame(10, $fileManagerDataTransfer->getFileInfo()->getSize());
    }

    /**
     * @return void
     */
    public function testSaveDirectory(): void
    {
        $fileDirectoryTransfer = new FileDirectoryTransfer();
        $fileDirectoryTransfer->setName('big directory');
        $fileDirectoryTransfer->setIsActive(true);
        $fileDirectoryTransfer->setPosition(1);
        $fileDirectoryId = $this->facade->saveDirectory($fileDirectoryTransfer);
        $this->assertIsInt($fileDirectoryId);

        $file = new FileTransfer();
        $file->setFileContent('big directory file');
        $file->setFileName('big_directory.txt');
        $file->setFkFileDirectory($fileDirectoryId);

        $fileInfo = new FileInfoTransfer();
        $fileInfo->setVersionName('v10');
        $fileInfo->setVersion(10);
        $fileInfo->setSize(17);
        $fileInfo->setStorageFileName('big_directory.txt');
        $fileInfo->setType('text');
        $fileInfo->setExtension('txt');

        $fileManagerDataTransfer = new FileManagerDataTransfer();
        $fileManagerDataTransfer->setContent('new version of the file');
        $fileManagerDataTransfer->setFile($file);
        $fileManagerDataTransfer->setFileInfo($fileInfo);

        $fileManagerDataTransfer = $this->facade->saveFile($fileManagerDataTransfer);
        $this->assertIsInt($fileManagerDataTransfer->getFile()->getIdFile());
        $this->assertFileExists(
            $this->tester->getDocumentFullFileName(
                $fileDirectoryId . DIRECTORY_SEPARATOR . $fileManagerDataTransfer->getFile()->getIdFile() . '-v.1.txt',
            ),
        );
    }

    /**
     * @return void
     */
    public function testFindFileDirectoryTree(): void
    {
        $tree = $this->facade->findFileDirectoryTree();
        $this->assertInstanceOf(FileDirectoryTreeTransfer::class, $tree);

        foreach ($tree->getNodes()->getArrayCopy() as $node) {
            $this->assertInstanceOf(FileDirectoryTreeNodeTransfer::class, $node);
        }
    }

    /**
     * @return void
     */
    public function testUpdateFileDirectoryTreeHierarchy(): void
    {
        $tree = $this->facade->findFileDirectoryTree();

        $firstNode = $tree->getNodes()->getArrayCopy()[0];
        $secondNode = $firstNode->getChildren()->getArrayCopy()[0];
        $thirdNode = $tree->getNodes()->getArrayCopy()[1];

        $this->assertSame(2, $tree->getNodes()->count());
        $this->assertSame(1, $firstNode->getChildren()->count());
        $this->assertSame(0, $secondNode->getChildren()->count());
        $this->assertSame(0, $thirdNode->getChildren()->count());
        $this->assertSame($this->tester->getIdFirstFileDirectory(), $firstNode->getFileDirectory()->getIdFileDirectory());
        $this->assertSame($this->tester->getIdSubFileDirectory(), $secondNode->getFileDirectory()->getIdFileDirectory());
        $this->assertSame($this->tester->getIdSecondFileDirectory(), $thirdNode->getFileDirectory()->getIdFileDirectory());

        $firstNode = $tree->getNodes()->getArrayCopy()[0];
        $subNode = $tree->getNodes()->getArrayCopy()[0]->getChildren()[0];
        $thirdNode = $tree->getNodes()->getArrayCopy()[1];
        $subNode->addChild($thirdNode);

        $newFileDirectoryTreeHierarchy = new FileDirectoryTreeTransfer();
        $newFileDirectoryTreeHierarchy->addNode($firstNode);

        $this->facade->updateFileDirectoryTreeHierarchy($newFileDirectoryTreeHierarchy);
        $tree = $this->facade->findFileDirectoryTree();

        $firstNode = $tree->getNodes()->getArrayCopy()[0];
        $secondNode = $firstNode->getChildren()->getArrayCopy()[0];
        $thirdNode = $secondNode->getChildren()->getArrayCopy()[0];

        $this->assertSame(1, $tree->getNodes()->count());
        $this->assertSame(1, $firstNode->getChildren()->count());
        $this->assertSame(1, $secondNode->getChildren()->count());
        $this->assertSame(0, $thirdNode->getChildren()->count());
        $this->assertEquals($this->tester->getIdFirstFileDirectory(), $firstNode->getFileDirectory()->getIdFileDirectory());
        $this->assertEquals($this->tester->getIdSubFileDirectory(), $secondNode->getFileDirectory()->getIdFileDirectory());
        $this->assertEquals($this->tester->getIdSecondFileDirectory(), $thirdNode->getFileDirectory()->getIdFileDirectory());
    }

    /**
     * @return void
     */
    public function testDeleteFileDirectory(): void
    {
        $this->assertTrue(
            $this->facade->deleteFileDirectory($this->tester->getIdFirstFileDirectory()),
        );
    }

    /**
     * @return void
     */
    public function testSaveMimeType(): void
    {
        $mimeTypeTransfer = $this->findMimeTypeById($this->tester->getIdMimeType())
            ->setName('image/jpeg')
            ->setComment('test')
            ->setIsAllowed(false);

        if ($this->hasExtensionsField()) {
            $mimeTypeTransfer->setExtensions(['png', 'jpg']);
        }

        $mimeTypeResponseTransfer = $this->facade->saveMimeType($mimeTypeTransfer);

        $this->assertInstanceOf(MimeTypeResponseTransfer::class, $mimeTypeResponseTransfer);
        $this->assertTrue($mimeTypeResponseTransfer->getIsSuccessful());
        $this->assertEquals($mimeTypeTransfer, $mimeTypeResponseTransfer->getMimeType());
        $this->assertEquals($mimeTypeTransfer, $this->findMimeTypeById($this->tester->getIdMimeType()));
    }

    /**
     * @return void
     */
    public function testUpdateMimeTypeSettings(): void
    {
        $mimeTypeCollectionTransfer = new MimeTypeCollectionTransfer();
        $mimeTypeTransfer = $this->findMimeTypeById($this->tester->getIdMimeType());

        $this->assertTrue($mimeTypeTransfer->getIsAllowed());

        $mimeTypeTransfer->setIsAllowed(false);
        $mimeTypeCollectionTransfer->addMimeType($mimeTypeTransfer);

        $this->facade->updateMimeTypeSettings($mimeTypeCollectionTransfer);

        $mimeTypeTransfer = $this->findMimeTypeById($this->tester->getIdMimeType());
        $this->assertFalse($mimeTypeTransfer->getIsAllowed());
    }

    /**
     * @return void
     */
    public function testDeleteMimeType(): void
    {
        $mimeTypeTransfer = $this->findMimeTypeById($this->tester->getIdMimeType());

        $mimeTypeResponseTransfer = $this->facade->deleteMimeType($mimeTypeTransfer);

        $this->assertInstanceOf(MimeTypeResponseTransfer::class, $mimeTypeResponseTransfer);
        $this->assertTrue($mimeTypeResponseTransfer->getIsSuccessful());
        $this->assertNull($this->findMimeTypeById(1)->getIdMimeType());
    }

    /**
     * @return void
     */
    public function testGetFilesByIds(): void
    {
        // Arrange
        $idFiles = $this->tester->insertFilesCollection(9);

        // Act
        $fileManagerDataTransfers = $this->facade->getFilesByIds($idFiles);

        // Assert
        $this->assertCount(9, $fileManagerDataTransfers);

        foreach ($fileManagerDataTransfers as $fileManagerDataTransfer) {
            $this->assertInstanceOf(FileManagerDataTransfer::class, $fileManagerDataTransfer);
            $this->assertInstanceOf(FileTransfer::class, $fileManagerDataTransfer->getFile());
            $this->assertInstanceOf(FileInfoTransfer::class, $fileManagerDataTransfer->getFileInfo());
            $this->assertContains($fileManagerDataTransfer->getFile()->getIdFile(), $idFiles);
        }
    }

    /**
     * @return void
     */
    public function testGetFileInfoVersionsCountReturnsCorrectNumberOfFileVersions(): void
    {
        // Arrange
        $this->tester->insertDbRecords();

        // Act
        $count = $this->facade->getFileInfoVersionsCount($this->tester->getIdFile());

        // Assert
        $this->assertSame(2, $count);
    }

    /**
     * @param int $idMimeType
     *
     * @return \Generated\Shared\Transfer\MimeTypeTransfer
     */
    protected function findMimeTypeById(int $idMimeType): MimeTypeTransfer
    {
        $mimeTypeTransfer = new MimeTypeTransfer();
        $mimeTypeEntity = SpyMimeTypeQuery::create()->findOneByIdMimeType($idMimeType);

        if ($mimeTypeEntity === null) {
            return $mimeTypeTransfer;
        }

        $mimeTypeData = $mimeTypeEntity->toArray();
        $mimeTypeData[MimeTypeTransfer::EXTENSIONS] = [];
        if ($this->hasExtensionsField() && $mimeTypeEntity->getExtensions()) {
            $mimeTypeData[MimeTypeTransfer::EXTENSIONS] = $this->tester->getLocator()->utilEncoding()->service()->decodeJson($mimeTypeEntity->getExtensions());
        }
        $mimeTypeTransfer->fromArray($mimeTypeData);

        return $mimeTypeTransfer;
    }

    /**
     * @deprecated Will be removed in the next major without replacement.
     *
     * @return bool
     */
    protected function hasExtensionsField(): bool
    {
        return property_exists(SpyMimeType::class, 'extensions');
    }

    /**
     * @return \Spryker\Zed\FileManagerExtension\Dependency\Plugin\FileManagerDataCollectionExpanderPreSavePluginInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFileManagerDataCollectionExpanderPreSavePluginMock(): FileManagerDataCollectionExpanderPreSavePluginInterface|MockObject
    {
        $fileManagerDataCollectionExpanderPreSavePluginMock = $this
            ->getMockBuilder(FileManagerDataCollectionExpanderPreSavePluginInterface::class)
            ->getMock();

        $fileManagerDataCollectionExpanderPreSavePluginMock
            ->expects($this->once())
            ->method('expand')
            ->willReturnCallback(function (FileManagerDataCollectionTransfer $fileManagerDataCollectionTransfer) {
                return $fileManagerDataCollectionTransfer;
            });

        return $fileManagerDataCollectionExpanderPreSavePluginMock;
    }

    /**
     * @return \Spryker\Zed\FileManagerExtension\Dependency\Plugin\FileManagerDataCollectionExpanderPluginInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFileManagerDataCollectionExpanderPluginMock(): FileManagerDataCollectionExpanderPluginInterface|MockObject
    {
        $fileManagerDataCollectionExpanderPluginMock = $this
            ->getMockBuilder(FileManagerDataCollectionExpanderPluginInterface::class)
            ->getMock();

        $fileManagerDataCollectionExpanderPluginMock
            ->expects($this->once())
            ->method('expand')
            ->willReturnCallback(function (FileManagerDataCollectionTransfer $fileManagerDataCollectionTransfer) {
                return $fileManagerDataCollectionTransfer;
            });

        return $fileManagerDataCollectionExpanderPluginMock;
    }

    /**
     * @return \Generated\Shared\Transfer\FileManagerDataTransfer
     */
    public function createFileManagerDataTransfer(): FileManagerDataTransfer
    {
        $fileInfo = new FileInfoTransfer();
        $fileInfo->setVersionName('v10');
        $fileInfo->setVersion(10);
        $fileInfo->setSize(17);
        $fileInfo->setStorageFileName('new_customer.txt');
        $fileInfo->setType('text');
        $fileInfo->setExtension('txt');

        $file = new FileTransfer();
        $file->setFileContent('new customer file');
        $file->setFileName('new%customer.txt');

        $fileManagerDataTransfer = new FileManagerDataTransfer();
        $fileManagerDataTransfer->setContent('new version of the file');
        $fileManagerDataTransfer->setFile($file);
        $fileManagerDataTransfer->setFileInfo($fileInfo);

        return $fileManagerDataTransfer;
    }
}
