<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\SspServiceManagement\Communication\Plugin\ProductPageSearch;

use Generated\Shared\Transfer\ProductPageSearchTransfer;
use Spryker\Shared\ProductPageSearch\ProductPageSearchConfig;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\ProductPageSearchExtension\Dependency\Plugin\ProductPageDataExpanderPluginInterface;

/**
 * @method \SprykerFeature\Zed\SspServiceManagement\Communication\SspServiceManagementCommunicationFactory getFactory()
 * @method \SprykerFeature\Zed\SspServiceManagement\Business\SspServiceManagementFacadeInterface getFacade()
 * @method \SprykerFeature\Zed\SspServiceManagement\SspServiceManagementConfig getConfig()
 */
class ProductAbstractTypeProductPageDataExpanderPlugin extends AbstractPlugin implements ProductPageDataExpanderPluginInterface
{
    /**
     * {@inheritDoc}
     * - Expands the provided ProductAbstractPageSearch transfer object's data by product abstract types.
     *
     * @api
     *
     * @param array<string, \Generated\Shared\Transfer\ProductPayloadTransfer> $productData
     * @param \Generated\Shared\Transfer\ProductPageSearchTransfer $productAbstractPageSearchTransfer
     *
     * @return void
     */
    public function expandProductPageData(array $productData, ProductPageSearchTransfer $productAbstractPageSearchTransfer): void
    {
        $productAbstractPageSearchTransfer->setProductAbstractTypes(
            $productData[ProductPageSearchConfig::PRODUCT_ABSTRACT_PAGE_LOAD_DATA]->getProductAbstractTypes(),
        );
    }
}
