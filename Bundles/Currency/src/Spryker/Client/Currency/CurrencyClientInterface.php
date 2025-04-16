<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\Currency;

/**
 * @method \Spryker\Client\Currency\CurrencyFactory getFactory()
 */
interface CurrencyClientInterface
{
    /**
     * Specification:
     *  - Reads currency data for given ISO code, it does not make Zed call so it wont have foreign keys to currency table.
     *
     * @api
     *
     * @param string $isoCode
     *
     * @return \Generated\Shared\Transfer\CurrencyTransfer
     */
    public function fromIsoCode($isoCode);

    /**
     * Specification:
     *  - Returns current customer session selected currency.
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\CurrencyTransfer
     */
    public function getCurrent();

    /**
     * Specification:
     * - Executes {@link \Spryker\Client\CurrencyExtension\Dependency\Plugin\CurrentCurrencyIsoCodePreCheckPluginInterface} plugins to check if currency can be changed.
     * - Sets selected currency to customer session.
     * - Calls currency post change plugins.
     *
     * @api
     *
     * @param string $currencyIsoCode
     *
     * @return void
     */
    public function setCurrentCurrencyIsoCode(string $currencyIsoCode): void;

    /**
     * Specification:
     * - Returns a list of currency codes available for current store.
     *
     * @api
     *
     * @return array<string>
     */
    public function getCurrencyIsoCodes(): array;
}
