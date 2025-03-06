<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\SspFileManagement\Persistence\Saver;

use Generated\Shared\Transfer\FileAttachmentTransfer;

interface FileAttachmentSaverInterface
{
    /**
     * @param \Generated\Shared\Transfer\FileAttachmentTransfer $fileAttachmentTransfer
     *
     * @return \Generated\Shared\Transfer\FileAttachmentTransfer
     */
    public function save(FileAttachmentTransfer $fileAttachmentTransfer): FileAttachmentTransfer;
}
