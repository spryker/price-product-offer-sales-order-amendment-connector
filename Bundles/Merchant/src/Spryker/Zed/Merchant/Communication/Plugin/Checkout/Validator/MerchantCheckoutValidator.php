<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Merchant\Communication\Plugin\Checkout\Validator;

use Generated\Shared\Transfer\CheckoutErrorTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\MerchantCriteriaTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Merchant\Business\MerchantFacadeInterface;

class MerchantCheckoutValidator implements MerchantCheckoutValidatorInterface
{
    /**
     * @var string
     */
    protected const GLOSSARY_KEY_REMOVED_MERCHANT = 'merchant.message.removed';

    /**
     * @var string
     */
    protected const GLOSSARY_PARAM_SKU = '%sku%';

    /**
     * @var \Spryker\Zed\Merchant\Business\MerchantFacadeInterface
     */
    protected $merchantFacade;

    /**
     * @param \Spryker\Zed\Merchant\Business\MerchantFacadeInterface $merchantFacade
     */
    public function __construct(MerchantFacadeInterface $merchantFacade)
    {
        $this->merchantFacade = $merchantFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponseTransfer
     *
     * @return bool
     */
    public function checkCondition(QuoteTransfer $quoteTransfer, CheckoutResponseTransfer $checkoutResponseTransfer): bool
    {
        $validationPassed = true;
        $merchantTransfers = $this->getMerchantTransfersGroupedByMerchantReference($quoteTransfer);

        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            if (!$itemTransfer->getMerchantReference()) {
                continue;
            }

            if (!isset($merchantTransfers[$itemTransfer->getMerchantReference()])) {
                $checkoutErrorTransfer = (new CheckoutErrorTransfer())
                    ->setMessage(static::GLOSSARY_KEY_REMOVED_MERCHANT)
                    ->setParameters([static::GLOSSARY_PARAM_SKU => $itemTransfer->getSku()]);

                $checkoutResponseTransfer->addError($checkoutErrorTransfer);
                $validationPassed = false;
            }
        }

        if (!$validationPassed) {
            $checkoutResponseTransfer->setIsSuccess(false);
        }

        return $validationPassed;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array<string, \Generated\Shared\Transfer\MerchantTransfer>
     */
    protected function getMerchantTransfersGroupedByMerchantReference(QuoteTransfer $quoteTransfer): array
    {
        $merchantReferences = [];
        $merchantTransfers = [];

        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            if (!$itemTransfer->getMerchantReference()) {
                continue;
            }
            $merchantReferences[] = $itemTransfer->getMerchantReference();
        }

        if (!$merchantReferences) {
            return $merchantTransfers;
        }
        /** @var array<string> $merchantReferences */
        $merchantReferences = array_unique($merchantReferences);

        $merchantCollectionTransfer = $this->merchantFacade->get(
            (new MerchantCriteriaTransfer())
                ->setMerchantReferences($merchantReferences)
                ->setIsActive(true)
                ->setStore($quoteTransfer->getStore())
                ->setWithExpanders(false),
        );
        foreach ($merchantCollectionTransfer->getMerchants() as $merchantTransfer) {
            $merchantTransfers[$merchantTransfer->getMerchantReference()] = $merchantTransfer;
        }

        return $merchantTransfers;
    }
}
