<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\PaymentsRestApi;

use Spryker\Glue\Kernel\AbstractBundleConfig;

class PaymentsRestApiConfig extends AbstractBundleConfig
{
    /**
     * @var string
     */
    public const RESOURCE_TYPE_PAYMENTS = 'payments';

    /**
     * @var string
     */
    public const RESOURCE_PAYMENT_METHODS = 'payment-methods';

    /**
     * @var string
     */
    public const RESOURCE_TYPE_PAYMENT_CANCELLATIONS = 'payment-cancellations';

    /**
     * @var array<string, int>
     */
    protected const PAYMENT_METHOD_PRIORITY = [];

    /**
     * @var array<string>
     */
    protected const PAYMENT_REQUIRED_FIELDS = [
        'paymentMethod',
        'paymentProvider',
    ];

    /**
     * @var array<string, array<string, array<string>>>
     */
    protected const PAYMENT_METHOD_REQUIRED_FIELDS = [];

    /**
     * @api
     *
     * @param string $paymentProviderName
     * @param string $paymentMethodName
     *
     * @return array
     */
    public function getRequiredRequestDataForPaymentMethod(string $paymentProviderName, string $paymentMethodName): array
    {
        $paymentMethodRequiredFields = static::PAYMENT_METHOD_REQUIRED_FIELDS[$paymentProviderName][$paymentMethodName] ?? [];

        return array_merge(static::PAYMENT_REQUIRED_FIELDS, $paymentMethodRequiredFields);
    }

    /**
     * @api
     *
     * @return array<int>
     */
    public function getPaymentMethodPriority(): array
    {
        return static::PAYMENT_METHOD_PRIORITY;
    }
}
