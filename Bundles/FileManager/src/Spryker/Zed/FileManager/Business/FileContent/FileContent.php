<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\FileManager\Business\FileContent;

use Generated\Shared\Transfer\FileSystemContentTransfer;
use Generated\Shared\Transfer\FileSystemDeleteTransfer;
use Generated\Shared\Transfer\FileSystemQueryTransfer;
use Generated\Shared\Transfer\FileTransfer;
use Spryker\Zed\FileManager\Dependency\Service\FileManagerToFileSystemServiceInterface;
use Spryker\Zed\FileManager\FileManagerConfig;

class FileContent implements FileContentInterface
{
    /**
     * @var \Spryker\Zed\FileManager\Dependency\Service\FileManagerToFileSystemServiceInterface
     */
    protected $fileSystemService;

    /**
     * @var \Spryker\Zed\FileManager\FileManagerConfig
     */
    protected $config;

    /**
     * @param \Spryker\Zed\FileManager\Dependency\Service\FileManagerToFileSystemServiceInterface $fileSystemService
     * @param \Spryker\Zed\FileManager\FileManagerConfig $config
     */
    public function __construct(FileManagerToFileSystemServiceInterface $fileSystemService, FileManagerConfig $config)
    {
        $this->fileSystemService = $fileSystemService;
        $this->config = $config;
    }

    /**
     * @param \Generated\Shared\Transfer\FileTransfer $fileTransfer
     *
     * @return void
     */
    public function save(FileTransfer $fileTransfer)
    {
        $fileSystemName = $this->config->getStorageName();
        if ($fileTransfer->getFileInfo()->count()) {
            /** @var \Generated\Shared\Transfer\FileInfoTransfer $fileInfoTransfer */
            $fileInfoTransfer = $fileTransfer->getFileInfo()->getIterator()->current();
            $fileSystemName = $fileInfoTransfer->getStorageName() ?? $fileSystemName;
        }

        $fileSystemContentTransfer = new FileSystemContentTransfer();
        $fileSystemContentTransfer->setFileSystemName($fileSystemName);
        $fileSystemContentTransfer->setPath($fileTransfer->getFileName());
        $fileSystemContentTransfer->setContent($fileTransfer->getFileContent());

        $this->fileSystemService->write($fileSystemContentTransfer);
    }

    /**
     * @param string $fileName
     * @param string|null $storageName
     *
     * @return void
     */
    public function delete($fileName, ?string $storageName = null)
    {
        $fileSystemQueryTransfer = new FileSystemQueryTransfer();
        $fileSystemQueryTransfer->setFileSystemName($storageName ?? $this->config->getStorageName());
        $fileSystemQueryTransfer->setPath($fileName);

        if ($this->fileSystemService->has($fileSystemQueryTransfer)) {
            $fileSystemDeleteTransfer = new FileSystemDeleteTransfer();
            $fileSystemDeleteTransfer->fromArray($fileSystemQueryTransfer->toArray());
            $this->fileSystemService->delete($fileSystemDeleteTransfer);
        }
    }

    /**
     * @param string $fileName
     * @param string|null $storageName
     *
     * @return string
     */
    public function read($fileName, ?string $storageName = null)
    {
        $fileSystemQueryTransfer = new FileSystemQueryTransfer();
        $fileSystemQueryTransfer->setFileSystemName($storageName ?? $this->config->getStorageName());
        $fileSystemQueryTransfer->setPath($fileName);

        return $this->fileSystemService->read($fileSystemQueryTransfer);
    }
}
