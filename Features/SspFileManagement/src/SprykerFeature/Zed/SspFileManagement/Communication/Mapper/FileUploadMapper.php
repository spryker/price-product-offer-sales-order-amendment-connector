<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\SspFileManagement\Communication\Mapper;

use Exception;
use Generated\Shared\Transfer\FileInfoTransfer;
use Generated\Shared\Transfer\FileManagerDataTransfer;
use Generated\Shared\Transfer\FileTransfer;
use Generated\Shared\Transfer\FileUploadTransfer;
use SprykerFeature\Zed\SspFileManagement\SspFileManagementConfig;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploadMapper implements FileUploadMapperInterface
{
    /**
     * @param \SprykerFeature\Zed\SspFileManagement\SspFileManagementConfig $sspFileManagementConfig
     */
    public function __construct(protected SspFileManagementConfig $sspFileManagementConfig)
    {
    }

    /**
     * @param array<\Symfony\Component\HttpFoundation\File\UploadedFile> $uploadedFiles
     *
     * @return array<\Generated\Shared\Transfer\FileUploadTransfer>
     */
    public function mapUploadedFilesToFileUploadTransfers(array $uploadedFiles): array
    {
        $fileUploadTransfers = [];

        foreach ($uploadedFiles as $uploadedFile) {
            if (!($uploadedFile instanceof UploadedFile)) {
                continue;
            }

            $fileUploadTransfer = $this->mapUploadedFileToFileUploadTransfer($uploadedFile);
            $fileUploadTransfers[] = $fileUploadTransfer;
        }

        return $fileUploadTransfers;
    }

    /**
     * @param \Generated\Shared\Transfer\FileUploadTransfer $fileUploadTransfer
     *
     * @return \Generated\Shared\Transfer\FileTransfer
     */
    public function mapFileUploadTransferToFileTransfer(FileUploadTransfer $fileUploadTransfer): FileTransfer
    {
        $fileTransfer = new FileTransfer();
        $fileTransfer->setFileUpload($fileUploadTransfer);

        return $fileTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\FileTransfer $fileTransfer
     *
     * @return \Generated\Shared\Transfer\FileManagerDataTransfer
     */
    public function mapFileTransferToFileManagerDataTransfer(FileTransfer $fileTransfer): FileManagerDataTransfer
    {
        $fileManagerDataTransfer = new FileManagerDataTransfer();
        $this->setFileName($fileTransfer);

        $fileManagerDataTransfer->setFile($fileTransfer);
        $fileManagerDataTransfer->setFileInfo($this->mapFileTransferToFileInfoTransfer($fileTransfer));

        if ($fileTransfer->getFileUpload() !== null) {
            $fileManagerDataTransfer->setContent(
                $this->getFileContent($fileTransfer->getFileUpload()),
            );
        }

        $fileManagerDataTransfer->setFileLocalizedAttributes($fileTransfer->getLocalizedAttributes());

        return $fileManagerDataTransfer;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $uploadedFile
     *
     * @return \Generated\Shared\Transfer\FileUploadTransfer
     */
    protected function mapUploadedFileToFileUploadTransfer(UploadedFile $uploadedFile): FileUploadTransfer
    {
        $fileUploadTransfer = new FileUploadTransfer();
        $fileUploadTransfer
            ->setClientOriginalName($uploadedFile->getClientOriginalName())
            ->setClientOriginalExtension($uploadedFile->getClientOriginalExtension())
            ->setSize($uploadedFile->getSize())
            ->setMimeTypeName($uploadedFile->getMimeType())
            ->setRealPath($uploadedFile->getRealPath());

        return $fileUploadTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\FileTransfer $fileTransfer
     *
     * @return \Generated\Shared\Transfer\FileTransfer
     */
    protected function setFileName(FileTransfer $fileTransfer): FileTransfer
    {
        $fileUploadTransfer = $fileTransfer->getFileUpload();

        if ($fileUploadTransfer === null) {
            return $fileTransfer;
        }

        $fileTransfer->setFileName(
            $fileUploadTransfer->getClientOriginalName(),
        );

        return $fileTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\FileUploadTransfer $fileUploadTransfer
     *
     * @throws \Exception
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException
     *
     * @return string
     */
    protected function getFileContent(FileUploadTransfer $fileUploadTransfer): string
    {
        $realPath = $fileUploadTransfer->getRealPath();

        if ($realPath === null) {
            throw new Exception('Real path not found');
        }

        $fileContent = file_get_contents($realPath);

        if ($fileContent === false) {
            throw new FileNotFoundException($realPath);
        }

        return $fileContent;
    }

    /**
     * @param \Generated\Shared\Transfer\FileTransfer $fileTransfer
     *
     * @return \Generated\Shared\Transfer\FileInfoTransfer
     */
    protected function mapFileTransferToFileInfoTransfer(FileTransfer $fileTransfer): FileInfoTransfer
    {
        $fileInfo = new FileInfoTransfer();
        $fileUploadTransfer = $fileTransfer->getFileUpload();

        if ($fileUploadTransfer === null) {
            return $fileInfo;
        }

        $fileInfo->setExtension($fileUploadTransfer->getClientOriginalExtension());
        $fileInfo->setSize($fileUploadTransfer->getSize());
        $fileInfo->setType($fileUploadTransfer->getMimeTypeName());
        $fileInfo->setStorageName($this->sspFileManagementConfig->getStorageName());

        return $fileInfo;
    }
}
