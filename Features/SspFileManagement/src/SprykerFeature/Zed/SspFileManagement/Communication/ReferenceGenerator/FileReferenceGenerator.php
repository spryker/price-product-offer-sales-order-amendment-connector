<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\SspFileManagement\Communication\ReferenceGenerator;

use Generated\Shared\Transfer\FileTransfer;
use Generated\Shared\Transfer\SequenceNumberSettingsTransfer;
use Spryker\Zed\SequenceNumber\Business\SequenceNumberFacadeInterface;
use SprykerFeature\Zed\SspFileManagement\SspFileManagementConfig;

class FileReferenceGenerator implements FileReferenceGeneratorInterface
{
    /**
     * @param \Spryker\Zed\SequenceNumber\Business\SequenceNumberFacadeInterface $sequenceNumberFacade
     * @param \SprykerFeature\Zed\SspFileManagement\SspFileManagementConfig $config
     */
    public function __construct(
        protected SequenceNumberFacadeInterface $sequenceNumberFacade,
        protected SspFileManagementConfig $config
    ) {
    }

    /**
     * @param \Generated\Shared\Transfer\FileTransfer $fileTransfer
     *
     * @return string
     */
    public function generateFileReference(FileTransfer $fileTransfer): string
    {
        $sequenceNumberSettingsTransfer = new SequenceNumberSettingsTransfer();
        $sequenceNumberSettingsTransfer
            ->setName($this->config->getFileSequenceNumberName())
            ->setPrefix($this->config->getFileSequenceNumberPrefix());

        return $this->sequenceNumberFacade->generate($sequenceNumberSettingsTransfer);
    }
}
