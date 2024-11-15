<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AvailabilityCartConnector\Business;

use Generated\Shared\Transfer\CartChangeTransfer;
use Generated\Shared\Transfer\CartPreCheckResponseTransfer;

/**
 * @method \Spryker\Zed\AvailabilityCartConnector\Business\AvailabilityCartConnectorBusinessFactory getFactory()
 */
interface AvailabilityCartConnectorFacadeInterface
{
    /**
     * Specification:
     *  - Executes `CartItemQuantityCounterStrategyPluginInterface` plugins.
     *  - Checks if items in CartChangeTransfer are sellable.
     *  - In case `ItemTransfer.amount` is defined, item availability check will be ignored.
     *  - Returns transfer with error message and isSuccess flag set to false when some of items are not available.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CartChangeTransfer $cartChangeTransfer
     *
     * @return \Generated\Shared\Transfer\CartPreCheckResponseTransfer
     */
    public function checkCartAvailabilityBatch(CartChangeTransfer $cartChangeTransfer): CartPreCheckResponseTransfer;

    /**
     * Specification:
     *  - Executes `CartItemQuantityCounterStrategyPluginInterface` plugins.
     *  - Checks if items in CartChangeTransfer are sellable.
     *  - In case `ItemTransfer.amount` was defined, item availability check will be ignored.
     *  - Returns transfer with error message and isSuccess flag set to false when some of items are not available.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CartChangeTransfer $cartChangeTransfer
     *
     * @return \Generated\Shared\Transfer\CartPreCheckResponseTransfer
     */
    public function checkCartAvailability(CartChangeTransfer $cartChangeTransfer);

    /**
     * Specification:
     * - Requires `CartChangeTransfer.quote.store` to be set.
     * - Calculates items quantity for each item in the cart.
     * - Executes a stack of {@link \Spryker\Zed\AvailabilityCartConnectorExtension\Dependency\Plugin\CartItemQuantityCounterStrategyPluginInterface} plugins.
     * - Ignores items with `ItemTransfer.amount` defined.
     * - Filters out items from `CartChangeTransfer` that are not sellable.
     * - Adds a message for each unique item that is not sellable.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\CartChangeTransfer $cartChangeTransfer
     *
     * @return \Generated\Shared\Transfer\CartChangeTransfer
     */
    public function filterOutUnavailableCartChangeItems(CartChangeTransfer $cartChangeTransfer): CartChangeTransfer;
}
