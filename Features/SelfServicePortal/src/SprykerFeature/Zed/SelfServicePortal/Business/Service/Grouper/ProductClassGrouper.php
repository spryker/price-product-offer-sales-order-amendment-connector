<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\SelfServicePortal\Business\Service\Grouper;

class ProductClassGrouper implements ProductClassGrouperInterface
{
    /**
     * @param array<string, array<\Generated\Shared\Transfer\ProductClassTransfer>> $productClassesBySku
     * @param array<int, string> $salesOrderItemIdToSkuMap
     *
     * @return array<int, array<\Generated\Shared\Transfer\ProductClassTransfer>>
     */
    public function groupProductClassesBySalesOrderItemIds(
        array $productClassesBySku,
        array $salesOrderItemIdToSkuMap
    ): array {
        $productClassesBySalesOrderItemId = [];

        foreach ($salesOrderItemIdToSkuMap as $salesOrderItemId => $sku) {
            if (isset($productClassesBySku[$sku])) {
                $productClassesBySalesOrderItemId[$salesOrderItemId] = $productClassesBySku[$sku];
            }
        }

        return $productClassesBySalesOrderItemId;
    }
}
