<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\PersistentCart\Business\Model;

use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Generated\Shared\Transfer\PersistentCartChangeQuantityTransfer;
use Generated\Shared\Transfer\PersistentCartChangeTransfer;
use Generated\Shared\Transfer\QuoteResponseTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\PersistentCart\Dependency\Facade\PersistentCartToQuoteFacadeInterface;
use Spryker\Zed\PersistentCartExtension\Dependency\Plugin\QuoteItemFinderPluginInterface;

class CartOperation implements CartOperationInterface
{
    /**
     * @var \Spryker\Zed\PersistentCart\Business\Model\QuoteResponseExpanderInterface
     */
    protected $quoteResponseExpander;

    /**
     * @var \Spryker\Zed\PersistentCartExtension\Dependency\Plugin\QuoteItemFinderPluginInterface
     */
    protected $itemFinderPlugin;

    /**
     * @var \Spryker\Zed\PersistentCart\Business\Model\QuoteResolverInterface
     */
    protected $quoteResolver;

    /**
     * @var \Spryker\Zed\PersistentCart\Business\Model\QuoteItemOperationInterface
     */
    protected $quoteItemOperation;

    /**
     * @var \Spryker\Zed\PersistentCart\Dependency\Facade\PersistentCartToQuoteFacadeInterface
     */
    protected $quoteFacade;

    /**
     * @var list<\Spryker\Zed\PersistentCartExtension\Dependency\Plugin\QuotePostMergePluginInterface>
     */
    protected array $quotePostMergePlugins;

    /**
     * @param \Spryker\Zed\PersistentCartExtension\Dependency\Plugin\QuoteItemFinderPluginInterface $itemFinderPlugin
     * @param \Spryker\Zed\PersistentCart\Business\Model\QuoteResponseExpanderInterface $quoteResponseExpander
     * @param \Spryker\Zed\PersistentCart\Business\Model\QuoteResolverInterface $quoteResolver
     * @param \Spryker\Zed\PersistentCart\Business\Model\QuoteItemOperationInterface $quoteItemOperations
     * @param \Spryker\Zed\PersistentCart\Dependency\Facade\PersistentCartToQuoteFacadeInterface $quoteFacade
     * @param list<\Spryker\Zed\PersistentCartExtension\Dependency\Plugin\QuotePostMergePluginInterface> $quotePostMergePlugins
     */
    public function __construct(
        QuoteItemFinderPluginInterface $itemFinderPlugin,
        QuoteResponseExpanderInterface $quoteResponseExpander,
        QuoteResolverInterface $quoteResolver,
        QuoteItemOperationInterface $quoteItemOperations,
        PersistentCartToQuoteFacadeInterface $quoteFacade,
        array $quotePostMergePlugins
    ) {
        $this->quoteResponseExpander = $quoteResponseExpander;
        $this->itemFinderPlugin = $itemFinderPlugin;
        $this->quoteResolver = $quoteResolver;
        $this->quoteItemOperation = $quoteItemOperations;
        $this->quoteFacade = $quoteFacade;
        $this->quotePostMergePlugins = $quotePostMergePlugins;
    }

    /**
     * @param \Generated\Shared\Transfer\PersistentCartChangeTransfer $persistentCartChangeTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteResponseTransfer
     */
    public function add(PersistentCartChangeTransfer $persistentCartChangeTransfer): QuoteResponseTransfer
    {
        $persistentCartChangeTransfer->requireCustomer();

        $quoteResponseTransfer = $this->quoteResolver->resolveCustomerQuote(
            (int)$persistentCartChangeTransfer->getIdQuote(),
            $persistentCartChangeTransfer->getCustomer(),
            $persistentCartChangeTransfer->getQuoteUpdateRequestAttributes(),
        );
        if (!$quoteResponseTransfer->getIsSuccessful()) {
            return $quoteResponseTransfer;
        }

        $quoteTransfer = $this->mergeQuotes(
            $quoteResponseTransfer->getQuoteTransfer(),
            $persistentCartChangeTransfer->getQuote(),
        );

        return $this->quoteItemOperation->addItems((array)$persistentCartChangeTransfer->getItems(), $quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\PersistentCartChangeTransfer $persistentCartChangeTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteResponseTransfer
     */
    public function addValid(PersistentCartChangeTransfer $persistentCartChangeTransfer): QuoteResponseTransfer
    {
        $persistentCartChangeTransfer->requireCustomer();

        $quoteResponseTransfer = $this->quoteResolver->resolveCustomerQuote(
            (int)$persistentCartChangeTransfer->getIdQuote(),
            $persistentCartChangeTransfer->getCustomer(),
            $persistentCartChangeTransfer->getQuoteUpdateRequestAttributes(),
        );
        if (!$quoteResponseTransfer->getIsSuccessful()) {
            return $quoteResponseTransfer;
        }

        $quoteTransfer = $this->mergeQuotes(
            $quoteResponseTransfer->getQuoteTransfer(),
            $persistentCartChangeTransfer->getQuote(),
        );

        return $this->quoteItemOperation->addValidItems((array)$persistentCartChangeTransfer->getItems(), $quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\PersistentCartChangeTransfer $persistentCartChangeTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteResponseTransfer
     */
    public function remove(PersistentCartChangeTransfer $persistentCartChangeTransfer): QuoteResponseTransfer
    {
        $persistentCartChangeTransfer->requireCustomer();

        $quoteResponseTransfer = $this->quoteResolver->resolveCustomerQuote(
            $persistentCartChangeTransfer->getIdQuote(),
            $persistentCartChangeTransfer->getCustomer(),
        );
        if (!$quoteResponseTransfer->getIsSuccessful()) {
            return $quoteResponseTransfer;
        }

        $quoteTransfer = $this->mergeQuotes(
            $quoteResponseTransfer->getQuoteTransfer(),
            $persistentCartChangeTransfer->getQuote(),
        );

        $itemTransferList = [];
        foreach ($persistentCartChangeTransfer->getItems() as $itemTransfer) {
            $item = $this->findItemInQuote($itemTransfer, $quoteTransfer);
            if ($item) {
                $itemTransferList[] = $item;
            }
        }

        return $this->quoteItemOperation->removeItems($itemTransferList, $quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\PersistentCartChangeQuantityTransfer $persistentCartChangeQuantityTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteResponseTransfer
     */
    public function changeItemQuantity(PersistentCartChangeQuantityTransfer $persistentCartChangeQuantityTransfer): QuoteResponseTransfer
    {
        $persistentCartChangeQuantityTransfer->requireCustomer();

        $quoteResponseTransfer = $this->quoteResolver->resolveCustomerQuote(
            $persistentCartChangeQuantityTransfer->getIdQuote(),
            $persistentCartChangeQuantityTransfer->getCustomer(),
        );
        if (!$quoteResponseTransfer->getIsSuccessful()) {
            return $quoteResponseTransfer;
        }

        $quoteTransfer = $this->mergeQuotes(
            $quoteResponseTransfer->getQuoteTransfer(),
            $persistentCartChangeQuantityTransfer->getQuote(),
        );

        $itemTransfer = $persistentCartChangeQuantityTransfer->getItem();
        $quoteItemTransfer = $this->findItemInQuote($itemTransfer, $quoteTransfer);
        if (!$quoteItemTransfer) {
            $quoteResponseTransfer = new QuoteResponseTransfer();
            $quoteResponseTransfer->setQuoteTransfer($quoteTransfer);
            $quoteResponseTransfer->setIsSuccessful(false);

            return $quoteResponseTransfer;
        }
        if ($itemTransfer->getQuantity() === 0) {
            return $this->quoteItemOperation->removeItems([$quoteItemTransfer], $quoteTransfer);
        }

        $delta = abs($quoteItemTransfer->getQuantity() - $itemTransfer->getQuantity());
        if ($delta === 0) {
            $quoteResponseTransfer = new QuoteResponseTransfer();
            $quoteResponseTransfer->setQuoteTransfer($quoteTransfer);
            $quoteResponseTransfer->setIsSuccessful(false);

            return $quoteResponseTransfer;
        }

        $changeItemTransfer = clone $quoteItemTransfer;
        $changeItemTransfer->setQuantity($delta);
        if ($quoteItemTransfer->getQuantity() > $itemTransfer->getQuantity()) {
            return $this->quoteItemOperation->removeItems([$changeItemTransfer], $quoteTransfer);
        }

        return $this->quoteItemOperation->addItems([$changeItemTransfer], $quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\PersistentCartChangeTransfer $persistentCartChangeTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteResponseTransfer
     */
    public function updateQuantity(PersistentCartChangeTransfer $persistentCartChangeTransfer): QuoteResponseTransfer
    {
        $persistentCartChangeTransfer->requireCustomer();

        $persistentQuoteResponseTransfer = $this->quoteResolver->resolveCustomerQuote(
            $persistentCartChangeTransfer->getIdQuote(),
            $persistentCartChangeTransfer->getCustomer(),
        );

        if (!$persistentQuoteResponseTransfer->getIsSuccessful()) {
            return $persistentQuoteResponseTransfer;
        }

        $quoteTransfer = $this->mergeQuotes(
            (new QuoteTransfer())->fromArray($persistentQuoteResponseTransfer->getQuoteTransfer()->toArray(), true),
            $persistentCartChangeTransfer->getQuote(),
        );

        $itemsToAdding = $this->prepareItemsForAdding($persistentCartChangeTransfer, $quoteTransfer);
        $itemsToRemoval = $this->prepareItemsForRemoval($persistentCartChangeTransfer, $quoteTransfer);

        if (!$itemsToAdding && !$itemsToRemoval) {
            return $persistentQuoteResponseTransfer;
        }

        $quoteResponseTransfer = $this->executeUpdateQuantity($itemsToAdding, $itemsToRemoval, $quoteTransfer);

        if (!$quoteResponseTransfer->getIsSuccessful() && $itemsToAdding && $itemsToRemoval) {
            return $this->quoteFacade
                ->updateQuote($persistentQuoteResponseTransfer->getQuoteTransfer())
                ->setIsSuccessful(false);
        }

        return $quoteResponseTransfer;
    }

    /**
     * @param array<\Generated\Shared\Transfer\ItemTransfer> $itemsToAdding
     * @param array<\Generated\Shared\Transfer\ItemTransfer> $itemsToRemoval
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteResponseTransfer
     */
    protected function executeUpdateQuantity(
        array $itemsToAdding,
        array $itemsToRemoval,
        QuoteTransfer $quoteTransfer
    ): QuoteResponseTransfer {
        $quoteResponseTransfer = (new QuoteResponseTransfer())
            ->setIsSuccessful(true);

        if ($itemsToAdding) {
            $quoteResponseTransfer = $this->quoteItemOperation->addItems($itemsToAdding, $quoteTransfer);
        }

        if ($quoteResponseTransfer->getIsSuccessful() && $itemsToRemoval) {
            return $this->quoteItemOperation->removeItems($itemsToRemoval, $quoteTransfer);
        }

        return $quoteResponseTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\PersistentCartChangeTransfer $persistentCartChangeTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array<\Generated\Shared\Transfer\ItemTransfer>
     */
    protected function prepareItemsForAdding(PersistentCartChangeTransfer $persistentCartChangeTransfer, QuoteTransfer $quoteTransfer): array
    {
        $itemsToAdding = [];

        foreach ($persistentCartChangeTransfer->getItems() as $changeItem) {
            $quoteItem = $this->findItemInQuote($changeItem, $quoteTransfer);

            if (!$quoteItem || $changeItem->getQuantity() <= 0) {
                continue;
            }

            $delta = $changeItem->getQuantity() - $quoteItem->getQuantity();

            if ($delta <= 0) {
                continue;
            }

            $changeItemTransfer = clone $quoteItem;
            $changeItemTransfer->setQuantity($delta);

            $itemsToAdding[] = $changeItemTransfer;
        }

        return $itemsToAdding;
    }

    /**
     * @param \Generated\Shared\Transfer\PersistentCartChangeTransfer $persistentCartChangeTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array<\Generated\Shared\Transfer\ItemTransfer>
     */
    protected function prepareItemsForRemoval(PersistentCartChangeTransfer $persistentCartChangeTransfer, QuoteTransfer $quoteTransfer): array
    {
        $itemsToRemove = [];

        foreach ($persistentCartChangeTransfer->getItems() as $changeItem) {
            $quoteItem = $this->findItemInQuote($changeItem, $quoteTransfer);

            if (!$quoteItem) {
                continue;
            }

            if ($changeItem->getQuantity() === 0) {
                $itemsToRemove[] = $quoteItem;

                continue;
            }

            $delta = $changeItem->getQuantity() - $quoteItem->getQuantity();

            if ($delta >= 0) {
                continue;
            }

            $changeItemTransfer = clone $quoteItem;
            $changeItemTransfer->setQuantity(abs($delta));

            $itemsToRemove[] = $changeItemTransfer;
        }

        return $itemsToRemove;
    }

    /**
     * @param \Generated\Shared\Transfer\PersistentCartChangeQuantityTransfer $persistentCartChangeQuantityTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteResponseTransfer
     */
    public function decreaseItemQuantity(PersistentCartChangeQuantityTransfer $persistentCartChangeQuantityTransfer): QuoteResponseTransfer
    {
        $persistentCartChangeQuantityTransfer->requireCustomer();

        $quoteResponseTransfer = $this->quoteResolver->resolveCustomerQuote(
            $persistentCartChangeQuantityTransfer->getIdQuote(),
            $persistentCartChangeQuantityTransfer->getCustomer(),
        );
        if (!$quoteResponseTransfer->getIsSuccessful()) {
            return $quoteResponseTransfer;
        }

        $quoteTransfer = $this->mergeQuotes(
            $quoteResponseTransfer->getQuoteTransfer(),
            $persistentCartChangeQuantityTransfer->getQuote(),
        );

        $decreaseItemTransfer = $this->findItemInQuote($persistentCartChangeQuantityTransfer->getItem(), $quoteTransfer);
        if (!$decreaseItemTransfer || !$persistentCartChangeQuantityTransfer->getItem()->getQuantity()) {
            return $quoteResponseTransfer;
        }

        $itemTransfer = clone $decreaseItemTransfer;
        $itemTransfer->setQuantity(
            $persistentCartChangeQuantityTransfer->getItem()->getQuantity(),
        );

        return $this->quoteItemOperation->removeItems([$itemTransfer], $quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\PersistentCartChangeQuantityTransfer $persistentCartChangeQuantityTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteResponseTransfer
     */
    public function increaseItemQuantity(PersistentCartChangeQuantityTransfer $persistentCartChangeQuantityTransfer): QuoteResponseTransfer
    {
        $persistentCartChangeQuantityTransfer->requireCustomer();

        $quoteResponseTransfer = $this->quoteResolver->resolveCustomerQuote(
            $persistentCartChangeQuantityTransfer->getIdQuote(),
            $persistentCartChangeQuantityTransfer->getCustomer(),
        );
        if (!$quoteResponseTransfer->getIsSuccessful()) {
            return $quoteResponseTransfer;
        }

        $quoteTransfer = $this->mergeQuotes(
            $quoteResponseTransfer->getQuoteTransfer(),
            $persistentCartChangeQuantityTransfer->getQuote(),
        );

        $decreaseItemTransfer = $this->findItemInQuote($persistentCartChangeQuantityTransfer->getItem(), $quoteTransfer);
        if (!$decreaseItemTransfer || !$persistentCartChangeQuantityTransfer->getItem()->getQuantity()) {
            return $this->createQuoteItemNotFoundResult($quoteTransfer, $persistentCartChangeQuantityTransfer->getCustomer());
        }

        $itemTransfer = clone $decreaseItemTransfer;
        $itemTransfer->setQuantity(
            $persistentCartChangeQuantityTransfer->getItem()->getQuantity(),
        );

        return $this->quoteItemOperation->addItems([$itemTransfer], $quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteResponseTransfer
     */
    public function reloadItems(QuoteTransfer $quoteTransfer): QuoteResponseTransfer
    {
        $quoteTransfer->requireCustomer();
        $quoteResponseTransfer = $this->quoteResolver->resolveCustomerQuote(
            $quoteTransfer->getIdQuote(),
            $quoteTransfer->getCustomer(),
        );
        if (!$quoteResponseTransfer->getIsSuccessful()) {
            return $quoteResponseTransfer;
        }
        $customerQuoteTransfer = $quoteResponseTransfer->getQuoteTransfer();
        $quoteTransfer->fromArray($customerQuoteTransfer->modifiedToArray(), true);

        return $this->quoteItemOperation->reloadItems($quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteResponseTransfer
     */
    public function validate($quoteTransfer): QuoteResponseTransfer
    {
        $quoteTransfer->requireCustomer();
        $quoteResponseTransfer = $this->quoteResolver->resolveCustomerQuote(
            $quoteTransfer->getIdQuote(),
            $quoteTransfer->getCustomer(),
        );
        if (!$quoteResponseTransfer->getIsSuccessful()) {
            return $quoteResponseTransfer;
        }
        $customerQuoteTransfer = $quoteResponseTransfer->getQuoteTransfer();

        if ($this->quoteFacade->isQuoteLocked($customerQuoteTransfer)) {
            return $this->quoteResponseExpander->expand($quoteResponseTransfer);
        }

        $quoteTransfer->fromArray($customerQuoteTransfer->modifiedToArray(), true);

        return $this->quoteItemOperation->validate($quoteTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $persistentQuoteTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer|null $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function mergeQuotes(QuoteTransfer $persistentQuoteTransfer, ?QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        if (!$quoteTransfer) {
            return $persistentQuoteTransfer;
        }

        $currentQuoteTransfer = clone $quoteTransfer;

        return $this->executeQuotePostMergePlugins(
            $quoteTransfer->fromArray($persistentQuoteTransfer->modifiedToArray(), true),
            $currentQuoteTransfer,
        );
    }

    /**
     * @param \Generated\Shared\Transfer\ItemTransfer $itemTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\ItemTransfer|null
     */
    protected function findItemInQuote(ItemTransfer $itemTransfer, QuoteTransfer $quoteTransfer): ?ItemTransfer
    {
        return $this->itemFinderPlugin->findItem($quoteTransfer, $itemTransfer->getSku(), $itemTransfer->getGroupKey());
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\CustomerTransfer $customerTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteResponseTransfer
     */
    protected function createQuoteItemNotFoundResult(QuoteTransfer $quoteTransfer, CustomerTransfer $customerTransfer): QuoteResponseTransfer
    {
        $quoteResponseTransfer = new QuoteResponseTransfer();
        $quoteResponseTransfer->setCustomer($customerTransfer);
        $quoteResponseTransfer->setQuoteTransfer($quoteTransfer);
        $quoteResponseTransfer->setIsSuccessful(false);

        return $this->quoteResponseExpander->expand($quoteResponseTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $persistentQuoteTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $currentQuoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function executeQuotePostMergePlugins(
        QuoteTransfer $persistentQuoteTransfer,
        QuoteTransfer $currentQuoteTransfer
    ): QuoteTransfer {
        foreach ($this->quotePostMergePlugins as $quotePostMergePlugin) {
            $persistentQuoteTransfer = $quotePostMergePlugin->postMerge($persistentQuoteTransfer, $currentQuoteTransfer);
        }

        return $persistentQuoteTransfer;
    }
}
