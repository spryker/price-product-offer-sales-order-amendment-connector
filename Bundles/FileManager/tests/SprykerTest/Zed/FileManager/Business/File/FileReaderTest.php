<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\FileManager\Business\File;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\FileInfoTransfer;
use Generated\Shared\Transfer\FileManagerDataTransfer;
use Generated\Shared\Transfer\FileTransfer;
use Spryker\Zed\FileManager\Business\File\FileReader;
use Spryker\Zed\FileManager\Business\FileContent\FileContentInterface;
use Spryker\Zed\FileManager\Persistence\FileManagerRepositoryInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group FileManager
 * @group Business
 * @group File
 * @group FileReaderTest
 * Add your own group annotations below this line
 */
class FileReaderTest extends Unit
{
    /**
     * @return \Spryker\Zed\FileManager\Business\FileContent\FileContentInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createFileContentMock(): FileContentInterface
    {
        return $this->getMockBuilder(FileContentInterface::class)->getMock();
    }

    /**
     * @return \Spryker\Zed\FileManager\Persistence\FileManagerRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function createFileManagerRepositoryMock(): FileManagerRepositoryInterface
    {
        return $this->getMockBuilder(FileManagerRepositoryInterface::class)->getMock();
    }

    /**
     * @return \Generated\Shared\Transfer\FileTransfer
     */
    protected function getMockedFile(): FileTransfer
    {
        $fileTransfer = new FileTransfer();
        $fileTransfer->setFileName('test.txt');
        $fileTransfer->setIdFile(1);

        $fileTransfer->addFileInfo($this->getMockedFileInfo());

        return $fileTransfer;
    }

    /**
     * @return \Generated\Shared\Transfer\FileInfoTransfer
     */
    protected function getMockedFileInfo(): FileInfoTransfer
    {
        $fileInfoTransfer = new FileInfoTransfer();
        $fileInfoTransfer->setIdFileInfo(1);
        $fileInfoTransfer->setExtension('txt');
        $fileInfoTransfer->setVersionName('v. 1');
        $fileInfoTransfer->setVersion(1);
        $fileInfoTransfer->setSize(1024);
        $fileInfoTransfer->setStorageFileName('report.txt');

        return $fileInfoTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\FileManagerDataTransfer $fileManagerDataTransfer
     *
     * @return void
     */
    protected function assertFileInfo(FileManagerDataTransfer $fileManagerDataTransfer): void
    {
        $this->assertSame('the content of the file', $fileManagerDataTransfer->getContent());
        $this->assertSame('v. 1', $fileManagerDataTransfer->getFileInfo()->getVersionName());
        $this->assertSame(1024, $fileManagerDataTransfer->getFileInfo()->getSize());
        $this->assertSame(1, $fileManagerDataTransfer->getFileInfo()->getVersion());
        $this->assertSame('txt', $fileManagerDataTransfer->getFileInfo()->getExtension());
        $this->assertSame('report.txt', $fileManagerDataTransfer->getFileInfo()->getStorageFileName());
    }

    /**
     * @return void
     */
    public function testRead(): void
    {
        //Arrange
        $fileContentMock = $this->createFileContentMock();
        $fileManagerRepositoryMock = $this->createFileManagerRepositoryMock();

        // Expect
        $fileManagerRepositoryMock->expects($this->once())
            ->method('getFileByIdFileInfo')
            ->willReturn($this->getMockedFile());

        $fileContentMock
            ->method('read')
            ->willReturn('the content of the file');

        $fileReader = new FileReader(
            $fileManagerRepositoryMock,
            $fileContentMock,
            [],
        );

        //Act
        $this->assertFileInfo($fileReader->readFileByIdFileInfo(1));
    }

    /**
     * @return void
     */
    public function testReadLatestByFileId(): void
    {
        //Arrange
        $fileContentMock = $this->createFileContentMock();
        $fileManagerRepositoryMock = $this->createFileManagerRepositoryMock();

        // Expect
        $fileManagerRepositoryMock->expects($this->once())
            ->method('getLatestFileInfoByIdFile')
            ->willReturn($this->getMockedFileInfo());

        $fileContentMock->expects($this->once())
            ->method('read')
            ->willReturn('the content of the file');

        $fileReader = new FileReader(
            $fileManagerRepositoryMock,
            $fileContentMock,
            [],
        );

        //Act
        $this->assertFileInfo($fileReader->readLatestByFileId(1));
    }
}
