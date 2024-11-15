<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CartReorderRestApi\Dependency\Glue;

use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

class CartReorderRestApiToCartsRestApiResourceBridge implements CartReorderRestApiToCartsRestApiResourceInterface
{
    /**
     * @var \Spryker\Glue\CartsRestApi\CartsRestApiResourceInterface
     */
    protected $cartsRestApiResource;

    /**
     * @param \Spryker\Glue\CartsRestApi\CartsRestApiResourceInterface $cartsRestApiResource
     */
    public function __construct($cartsRestApiResource)
    {
        $this->cartsRestApiResource = $cartsRestApiResource;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createCartRestResponse(QuoteTransfer $quoteTransfer, RestRequestInterface $restRequest): RestResponseInterface
    {
        return $this->cartsRestApiResource->createCartRestResponse($quoteTransfer, $restRequest);
    }
}
