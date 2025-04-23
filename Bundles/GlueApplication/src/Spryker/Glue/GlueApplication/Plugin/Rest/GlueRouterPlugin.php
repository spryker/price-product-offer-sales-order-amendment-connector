<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\GlueApplication\Plugin\Rest;

use Spryker\Glue\Kernel\AbstractPlugin;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;

/**
 * @method \Spryker\Glue\GlueApplication\GlueApplicationFactory getFactory()
 */
class GlueRouterPlugin extends AbstractPlugin implements RequestMatcherInterface, UrlGeneratorInterface
{
    use GlueRouterPluginTrait;

    /**
     * Sets the request context.
     *
     * @param \Symfony\Component\Routing\RequestContext $context The context
     *
     * @return void
     */
    public function setContext(RequestContext $context)
    {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    protected function executeMatchRequest(Request $request): array
    {
        return $this->getFactory()
            ->createRestResourceRouter()
            ->matchRequest($request);
    }

    /**
     * {@inheritDoc}
     *
     * @return \Symfony\Component\Routing\RequestContext
     */
    protected function executeGetContext(): RequestContext
    {
        return new RequestContext();
    }

    /**
     * {@inheritDoc}
     *
     * @param string $name
     * @param array $parameters
     * @param int $referenceType
     *
     * @return string
     */
    protected function executeGenerate(string $name, array $parameters = [], int $referenceType = self::ABSOLUTE_PATH): string
    {
        return '';
    }
}
