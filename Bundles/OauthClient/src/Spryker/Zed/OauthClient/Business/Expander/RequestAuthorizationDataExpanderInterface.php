<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\OauthClient\Business\Expander;

use Generated\Shared\Transfer\AcpHttpRequestTransfer;
use Generated\Shared\Transfer\HttpRequestTransfer;
use Generated\Shared\Transfer\MessageAttributesTransfer;
use Generated\Shared\Transfer\PaymentAuthorizeRequestTransfer;

interface RequestAuthorizationDataExpanderInterface
{
    /**
     * @param \Generated\Shared\Transfer\MessageAttributesTransfer $messageAttributesTransfer
     *
     * @return \Generated\Shared\Transfer\MessageAttributesTransfer
     */
    public function expandMessageAttributes(
        MessageAttributesTransfer $messageAttributesTransfer
    ): MessageAttributesTransfer;

    /**
     * @param \Generated\Shared\Transfer\PaymentAuthorizeRequestTransfer $paymentAuthorizeRequestTransfer
     *
     * @return \Generated\Shared\Transfer\PaymentAuthorizeRequestTransfer
     */
    public function expandPaymentAuthorizeRequest(
        PaymentAuthorizeRequestTransfer $paymentAuthorizeRequestTransfer
    ): PaymentAuthorizeRequestTransfer;

    /**
     * @param \Generated\Shared\Transfer\AcpHttpRequestTransfer $acpHttpRequestTransfer
     *
     * @return \Generated\Shared\Transfer\AcpHttpRequestTransfer
     */
    public function expandAcpRequest(
        AcpHttpRequestTransfer $acpHttpRequestTransfer
    ): AcpHttpRequestTransfer;

    /**
     * @param \Generated\Shared\Transfer\HttpRequestTransfer $httpRequestTransfer
     *
     * @return \Generated\Shared\Transfer\HttpRequestTransfer
     */
    public function expandHttpChannelMessageReceiverRequest(HttpRequestTransfer $httpRequestTransfer): HttpRequestTransfer;
}
