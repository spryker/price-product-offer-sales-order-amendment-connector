<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Glue\GlueApplication\Stub;

use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResource;
use Spryker\Glue\GlueApplication\Rest\Request\Data\Metadata;
use Spryker\Glue\GlueApplication\Rest\Request\Data\MetadataInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\Version;
use Spryker\Glue\GlueApplication\Rest\Request\RequestBuilder;
use Symfony\Component\HttpFoundation\Request;

class RestRequest
{
    /**
     * @param string $method
     * @param string $resourceType
     * @param string $uri
     *
     * @return \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface
     */
    public function createRestRequest(string $method = Request::METHOD_GET, string $resourceType = 'test', string $uri = '/'): RestRequestInterface
    {
        $metadata = $this->createMetadata($method);

        $request = Request::create($uri);

        $restResource = new RestResource($resourceType, '1');

        return (new RequestBuilder($restResource))
            ->addMetadata($metadata)
            ->addHttpRequest($request)
            ->addPage(1, 5)
            ->build();
    }

    /**
     * @param string $method
     *
     * @return \Spryker\Glue\GlueApplication\Rest\Request\Data\MetadataInterface
     */
    public function createMetadata(string $method = Request::METHOD_GET): MetadataInterface
    {
        $version = new Version(1, 1);

        $metadata = new Metadata(
            'json',
            'json',
            $method,
            'DE',
            true,
            $version,
        );

        return $metadata;
    }
}
