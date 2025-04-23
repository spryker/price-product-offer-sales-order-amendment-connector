<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\GlueApplication\Rest;

use Exception;
use Generated\Shared\Transfer\RestErrorCollectionTransfer;
use Spryker\Glue\GlueApplication\Controller\ErrorControllerInterface;
use Spryker\Glue\GlueApplication\GlueApplicationConfig;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\GlueApplication\Rest\Request\FormattedControllerBeforeActionInterface;
use Spryker\Glue\GlueApplication\Rest\Request\HttpRequestValidatorInterface;
use Spryker\Glue\GlueApplication\Rest\Request\RequestFormatterInterface;
use Spryker\Glue\GlueApplication\Rest\Request\RestRequestValidatorInterface;
use Spryker\Glue\GlueApplication\Rest\Response\ResponseFormatterInterface;
use Spryker\Glue\GlueApplication\Rest\Response\ResponseHeadersInterface;
use Spryker\Glue\GlueApplication\Rest\User\RestUserValidatorInterface;
use Spryker\Glue\GlueApplication\Rest\User\UserProviderInterface;
use Spryker\Glue\Kernel\Controller\AbstractController;
use Spryker\Glue\Kernel\Controller\FormattedAbstractController;
use Spryker\Shared\Log\LoggerTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ControllerFilter implements ControllerFilterInterface
{
    use LoggerTrait;

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\Request\RequestFormatterInterface
     */
    protected $requestFormatter;

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\Response\ResponseFormatterInterface
     */
    protected $responseFormatter;

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\Response\ResponseHeadersInterface
     */
    protected $responseHeaders;

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\Request\HttpRequestValidatorInterface
     */
    protected $httpRequestValidator;

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\Request\RestRequestValidatorInterface
     */
    protected $restRequestValidator;

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\User\RestUserValidatorInterface
     */
    protected $restUserValidator;

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface
     */
    protected $restResourceBuilder;

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\ControllerCallbacksInterface
     */
    protected $controllerCallbacks;

    /**
     * @var \Spryker\Glue\GlueApplication\GlueApplicationConfig
     */
    protected $applicationConfig;

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\User\UserProviderInterface
     */
    protected $userProvider;

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\Request\FormattedControllerBeforeActionInterface
     */
    protected $formattedControllerBeforeAction;

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\RequestFormatterInterface $requestFormatter
     * @param \Spryker\Glue\GlueApplication\Rest\Response\ResponseFormatterInterface $responseFormatter
     * @param \Spryker\Glue\GlueApplication\Rest\Response\ResponseHeadersInterface $responseHeaders
     * @param \Spryker\Glue\GlueApplication\Rest\Request\HttpRequestValidatorInterface $httpRequestValidator
     * @param \Spryker\Glue\GlueApplication\Rest\Request\RestRequestValidatorInterface $restRequestValidator
     * @param \Spryker\Glue\GlueApplication\Rest\User\RestUserValidatorInterface $restUserValidator
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface $restResourceBuilder
     * @param \Spryker\Glue\GlueApplication\Rest\ControllerCallbacksInterface $controllerCallbacks
     * @param \Spryker\Glue\GlueApplication\GlueApplicationConfig $applicationConfig
     * @param \Spryker\Glue\GlueApplication\Rest\User\UserProviderInterface $userProvider
     * @param \Spryker\Glue\GlueApplication\Rest\Request\FormattedControllerBeforeActionInterface $formattedControllerBeforeAction
     */
    public function __construct(
        RequestFormatterInterface $requestFormatter,
        ResponseFormatterInterface $responseFormatter,
        ResponseHeadersInterface $responseHeaders,
        HttpRequestValidatorInterface $httpRequestValidator,
        RestRequestValidatorInterface $restRequestValidator,
        RestUserValidatorInterface $restUserValidator,
        RestResourceBuilderInterface $restResourceBuilder,
        ControllerCallbacksInterface $controllerCallbacks,
        GlueApplicationConfig $applicationConfig,
        UserProviderInterface $userProvider,
        FormattedControllerBeforeActionInterface $formattedControllerBeforeAction
    ) {
        $this->requestFormatter = $requestFormatter;
        $this->responseFormatter = $responseFormatter;
        $this->responseHeaders = $responseHeaders;
        $this->httpRequestValidator = $httpRequestValidator;
        $this->restRequestValidator = $restRequestValidator;
        $this->restUserValidator = $restUserValidator;
        $this->restResourceBuilder = $restResourceBuilder;
        $this->controllerCallbacks = $controllerCallbacks;
        $this->applicationConfig = $applicationConfig;
        $this->userProvider = $userProvider;
        $this->formattedControllerBeforeAction = $formattedControllerBeforeAction;
    }

    /**
     * @param \Spryker\Glue\Kernel\Controller\AbstractController $controller
     * @param string $action
     * @param \Symfony\Component\HttpFoundation\Request $httpRequest
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function filter(AbstractController $controller, string $action, Request $httpRequest): Response
    {
        try {
            $restErrorMessageTransfer = $this->httpRequestValidator->validate($httpRequest);
            if ($restErrorMessageTransfer) {
                return new Response($restErrorMessageTransfer->getDetail(), $restErrorMessageTransfer->getStatus());
            }

            if ($controller instanceof FormattedAbstractController) {
                $restErrorMessageTransfer = $this->formattedControllerBeforeAction->beforeAction($httpRequest);
                if ($restErrorMessageTransfer) {
                    return new Response($restErrorMessageTransfer->getDetail(), $restErrorMessageTransfer->getStatus());
                }

                $httpResponse = $controller->$action($httpRequest);

                if ($this->applicationConfig->getCorsAllowOrigin()) {
                    $httpResponse = $this->responseHeaders->addCorsAllowOriginHeader($httpResponse);
                }

                return $httpResponse;
            }

            $restRequest = $this->requestFormatter->formatRequest($httpRequest);
            $restErrorCollectionTransfer = $this->validateRequest($controller, $httpRequest, $restRequest);
            $restResponse = $this->getRestResponse($restRequest, $restErrorCollectionTransfer, $controller, $action);
            $httpResponse = $this->responseFormatter->format($restResponse, $restRequest);

            return $this->responseHeaders->addHeaders($httpResponse, $restResponse, $restRequest);
        } catch (Exception $exception) {
            return $this->handleException($exception);
        }
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     * @param \Generated\Shared\Transfer\RestErrorCollectionTransfer|null $restErrorCollectionTransfer
     * @param \Spryker\Glue\Kernel\Controller\AbstractController $controller
     * @param string $action
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    protected function getRestResponse(
        RestRequestInterface $restRequest,
        ?RestErrorCollectionTransfer $restErrorCollectionTransfer,
        AbstractController $controller,
        string $action
    ): RestResponseInterface {
        if (!$restErrorCollectionTransfer || !$restErrorCollectionTransfer->getRestErrors()->count()) {
            $restRequest = $this->userProvider->setUserToRestRequest($restRequest);
            $restUserValidationRestErrorCollectionTransfer = $this->restUserValidator->validate($restRequest);
            if ($restUserValidationRestErrorCollectionTransfer) {
                return $this->createErrorResponse($restUserValidationRestErrorCollectionTransfer);
            }

            return $this->executeAction($controller, $action, $restRequest);
        }

        return $this->createErrorResponse($restErrorCollectionTransfer);
    }

    /**
     * @param \Spryker\Glue\Kernel\Controller\AbstractController $controller
     * @param string $action
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    protected function processResource(
        AbstractController $controller,
        string $action,
        RestRequestInterface $restRequest
    ): RestResponseInterface {
        return $controller->$action($restRequest, $restRequest->getResource()->getAttributes());
    }

    /**
     * @param \Spryker\Glue\Kernel\Controller\AbstractController $controller
     * @param string $action
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    protected function executeAction(
        AbstractController $controller,
        string $action,
        RestRequestInterface $restRequest
    ): RestResponseInterface {
        $this->controllerCallbacks->beforeAction($action, $restRequest);

        if ($controller instanceof ErrorControllerInterface) {
            $restResponse = $controller->$action();
        } else {
            $restResponse = $this->processResource($controller, $action, $restRequest);
        }

        $this->controllerCallbacks->afterAction($action, $restRequest, $restResponse);

        return $restResponse;
    }

    /**
     * @param \Exception $exception
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleException(Exception $exception): Response
    {
        if ($this->applicationConfig->getIsRestDebugEnabled()) {
            throw $exception;
        }

        $this->logException($exception);

        return new Response(
            Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR],
            Response::HTTP_INTERNAL_SERVER_ERROR,
        );
    }

    /**
     * @param \Exception $exception
     *
     * @return void
     */
    protected function logException(Exception $exception): void
    {
        $this->getLogger()->error($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
    }

    /**
     * @param \Spryker\Glue\Kernel\Controller\AbstractController $controller
     * @param \Symfony\Component\HttpFoundation\Request $httpRequest
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Generated\Shared\Transfer\RestErrorCollectionTransfer|null
     */
    protected function validateRequest(AbstractController $controller, Request $httpRequest, RestRequestInterface $restRequest): ?RestErrorCollectionTransfer
    {
        $restErrorCollectionTransfer = null;

        /**
         * @description Skip validation for the OPTION method to not invalidate CORS requests.
         * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
         * @link https://developer.mozilla.org/en-US/docs/Glossary/Preflight_request
         * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/OPTIONS#preflighted_requests_in_cors
         */
        if ($httpRequest->getMethod() === Request::METHOD_OPTIONS) {
            return null;
        }

        if (!$controller instanceof ErrorControllerInterface) {
            $restErrorCollectionTransfer = $this->restRequestValidator->validate($httpRequest, $restRequest);
        }

        return $restErrorCollectionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\RestErrorCollectionTransfer $restErrorCollectionTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    protected function createErrorResponse(RestErrorCollectionTransfer $restErrorCollectionTransfer): RestResponseInterface
    {
        $restResponse = $this->restResourceBuilder->createRestResponse();
        foreach ($restErrorCollectionTransfer->getRestErrors() as $restErrorMessageTransfer) {
            $restResponse->addError($restErrorMessageTransfer);
        }

        return $restResponse;
    }
}
