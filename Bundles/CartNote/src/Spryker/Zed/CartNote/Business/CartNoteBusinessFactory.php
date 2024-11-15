<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CartNote\Business;

use Spryker\Zed\CartNote\Business\Expander\CartReorderExpander;
use Spryker\Zed\CartNote\Business\Expander\CartReorderExpanderInterface;
use Spryker\Zed\CartNote\Business\Hydrator\CartReorderItemHydrator;
use Spryker\Zed\CartNote\Business\Hydrator\CartReorderItemHydratorInterface;
use Spryker\Zed\CartNote\Business\Model\CartNoteSaver;
use Spryker\Zed\CartNote\Business\Model\CartNoteSaverInterface;
use Spryker\Zed\CartNote\Business\Model\QuoteCartNoteSetter;
use Spryker\Zed\CartNote\Business\Model\QuoteCartNoteSetterInterface;
use Spryker\Zed\CartNote\CartNoteDependencyProvider;
use Spryker\Zed\CartNote\Dependency\Facade\CartNoteToQuoteFacadeInterface;
use Spryker\Zed\CartNoteExtension\Dependency\Plugin\QuoteItemFinderPluginInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\CartNote\Persistence\CartNoteEntityManagerInterface getEntityManager()
 */
class CartNoteBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\CartNote\Business\Model\CartNoteSaverInterface
     */
    public function createCartNoteSaver(): CartNoteSaverInterface
    {
        return new CartNoteSaver($this->getEntityManager());
    }

    /**
     * @return \Spryker\Zed\CartNote\Business\Model\QuoteCartNoteSetterInterface
     */
    public function createQuoteCartNoteSetter(): QuoteCartNoteSetterInterface
    {
        return new QuoteCartNoteSetter($this->getQuoteFacade(), $this->getQuoteItemsFinderPlugin());
    }

    /**
     * @return \Spryker\Zed\CartNote\Business\Hydrator\CartReorderItemHydratorInterface
     */
    public function createCartReorderItemHydrator(): CartReorderItemHydratorInterface
    {
        return new CartReorderItemHydrator();
    }

    /**
     * @return \Spryker\Zed\CartNote\Business\Expander\CartReorderExpanderInterface
     */
    public function createCartReorderExpander(): CartReorderExpanderInterface
    {
        return new CartReorderExpander();
    }

    /**
     * @return \Spryker\Zed\CartNote\Dependency\Facade\CartNoteToQuoteFacadeInterface
     */
    public function getQuoteFacade(): CartNoteToQuoteFacadeInterface
    {
        return $this->getProvidedDependency(CartNoteDependencyProvider::FACADE_QUOTE);
    }

    /**
     * @return \Spryker\Zed\CartNoteExtension\Dependency\Plugin\QuoteItemFinderPluginInterface
     */
    protected function getQuoteItemsFinderPlugin(): QuoteItemFinderPluginInterface
    {
        return $this->getProvidedDependency(CartNoteDependencyProvider::PLUGIN_QUOTE_ITEMS_FINDER);
    }
}
