<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\SspServiceManagement\Communication\Form\DataProvider;

use Generated\Shared\Transfer\ItemMetadataTransfer;
use Generated\Shared\Transfer\ItemTransfer;

class ItemSchedulerFormDataProvider
{
    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     *
     * @return \Generated\Shared\Transfer\ItemTransfer
     */
    public function getData(ItemTransfer $itemTransfer): ItemTransfer
    {
        if (!$itemTransfer->getMetadata()) {
            $itemTransfer->setMetadata(new ItemMetadataTransfer());
        }

        return $itemTransfer;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions(): array
    {
        return [];
    }
}
