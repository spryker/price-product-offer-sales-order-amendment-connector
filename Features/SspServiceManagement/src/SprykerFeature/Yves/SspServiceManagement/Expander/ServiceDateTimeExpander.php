<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\SspServiceManagement\Expander;

use Generated\Shared\Transfer\ItemMetadataTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use SprykerFeature\Yves\SspServiceManagement\Widget\ShipmentTypeServicePointSelectorWidget;

class ServiceDateTimeExpander implements ServiceDateTimeExpanderInterface
{
    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param array<string, mixed> $params
     *
     * @return \Generated\Shared\Transfer\ItemTransfer
     */
    public function expandItemTransferWithServiceDateTime(ItemTransfer $itemTransfer, array $params): ItemTransfer
    {
        if (!isset($params[ShipmentTypeServicePointSelectorWidget::DEFAULT_FORM_FIELD_ITEM_METADATA_SCHEDULED_AT])) {
            return $itemTransfer;
        }

        $scheduledAt = $params[ShipmentTypeServicePointSelectorWidget::DEFAULT_FORM_FIELD_ITEM_METADATA_SCHEDULED_AT] ?: null;

        if (!$scheduledAt) {
            return $itemTransfer;
        }

        $itemMetadataTransfer = $itemTransfer->getMetadata() ?? new ItemMetadataTransfer();
        $itemMetadataTransfer->setScheduledAt($scheduledAt);
        $itemTransfer->setMetadata($itemMetadataTransfer);

        return $itemTransfer;
    }
}
