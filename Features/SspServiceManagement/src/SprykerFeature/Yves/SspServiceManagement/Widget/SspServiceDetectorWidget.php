<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\SspServiceManagement\Widget;

use Generated\Shared\Transfer\ProductViewTransfer;
use LogicException;
use Spryker\Yves\Kernel\Widget\AbstractWidget;

/**
 * @method \SprykerFeature\Yves\SspServiceManagement\SspServiceManagementFactory getFactory()
 * @method \SprykerFeature\Yves\SspServiceManagement\SspServiceManagementConfig getConfig()
 */
class SspServiceDetectorWidget extends AbstractWidget
{
    /**
     * @var string
     */
    protected const PARAMETER_IS_SERVICE = 'isService';

    /**
     * @var string
     */
    protected const PARAMETER_PRODUCT_ABSTRACT_TYPES = 'product-abstract-types';

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer|array<string|mixed> $productData
     */
    public function __construct(array|ProductViewTransfer $productData)
    {
        $this->addIsServiceParameter($productData);
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer|array<string|mixed> $productData
     *
     * @return void
     */
    protected function addIsServiceParameter(array|ProductViewTransfer $productData): void
    {
        $this->addParameter(static::PARAMETER_IS_SERVICE, $this->isSspService($productData));
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'SspServiceDetectorWidget';
    }

    /**
     * @throws \LogicException
     *
     * @return string
     */
    public static function getTemplate(): string
    {
        throw new LogicException('This widget should only be used as service detector.');
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer|array<string|mixed> $productData
     *
     * @return bool
     */
    protected function isSspService(array|ProductViewTransfer $productData): bool
    {
        $productServiceTypeName = $this->getConfig()->getProductServiceTypeName();

        $productTypes = $this->getProductTypes($productData);

        if (!$productTypes) {
            return false;
        }

        return in_array($productServiceTypeName, $productTypes, true);
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer|array<string|mixed> $productData
     *
     * @return array<string>
     */
    protected function getProductTypes(array|ProductViewTransfer $productData): array
    {
        if (is_array($productData)) {
            return $productData[static::PARAMETER_PRODUCT_ABSTRACT_TYPES] ?? [];
        }

        /**
         * @var \Generated\Shared\Transfer\ProductViewTransfer $productData
         */
        return $productData->getProductTypes();
    }
}
