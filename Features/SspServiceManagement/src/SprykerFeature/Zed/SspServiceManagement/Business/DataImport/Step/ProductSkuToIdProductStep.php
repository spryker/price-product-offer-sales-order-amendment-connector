<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace SprykerFeature\Zed\SspServiceManagement\Business\DataImport\Step;

use Orm\Zed\Product\Persistence\SpyProductQuery;
use Spryker\Zed\DataImport\Business\Exception\EntityNotFoundException;
use Spryker\Zed\DataImport\Business\Model\DataImportStep\DataImportStepInterface;
use Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface;
use SprykerFeature\Zed\SspServiceManagement\Business\DataImport\DataSet\ProductShipmentTypeDataSetInterface;

class ProductSkuToIdProductStep implements DataImportStepInterface
{
    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductQuery $productQuery
     */
    public function __construct(protected SpyProductQuery $productQuery)
    {
    }

    /**
     * @param \Spryker\Zed\DataImport\Business\Model\DataSet\DataSetInterface $dataSet
     *
     * @throws \Spryker\Zed\DataImport\Business\Exception\EntityNotFoundException
     *
     * @return void
     */
    public function execute(DataSetInterface $dataSet): void
    {
        $productSku = $dataSet[ProductShipmentTypeDataSetInterface::CONCRETE_SKU];

        $productEntity = $this->productQuery
            ->clear()
            ->filterBySku($productSku)
            ->findOne();

        if (!$productEntity) {
            throw new EntityNotFoundException(sprintf('Product with SKU "%s" not found.', $productSku));
        }

        $dataSet[ProductShipmentTypeDataSetInterface::ID_PRODUCT] = $productEntity->getIdProduct();
    }
}
