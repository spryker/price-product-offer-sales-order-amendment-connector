<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\ErrorHandler;

use ErrorException;
use ReflectionProperty;
use Spryker\Service\UtilSanitize\UtilSanitizeServiceInterface;
use Spryker\Shared\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ErrorHandler
{
    /**
     * @var string
     */
    public const ZED = 'ZED';

    public const EXIT_CODE_ERROR = -1;

    /**
     * @var string
     */
    protected const METHOD_NAME_GET_STATUS_CODE = 'getStatusCode';

    /**
     * @var string
     */
    protected const HEADER_404 = 'HTTP/1.0 404 Not Found';

    /**
     * @var string
     */
    protected const HEADER_500 = 'HTTP/1.0 500 Internal Server Error';

    /**
     * @var \Spryker\Shared\ErrorHandler\ErrorLoggerInterface
     */
    protected $errorLogger;

    /**
     * @var \Spryker\Shared\ErrorHandler\ErrorRenderer\ErrorRendererInterface
     */
    protected $errorRenderer;

    /**
     * @var \Spryker\Service\UtilSanitize\UtilSanitizeServiceInterface
     */
    protected $utilSanitizeService;

    /**
     * @param \Spryker\Shared\ErrorHandler\ErrorLoggerInterface $errorLogger
     * @param \Spryker\Shared\ErrorHandler\ErrorRenderer\ErrorRendererInterface $errorRenderer
     * @param \Spryker\Service\UtilSanitize\UtilSanitizeServiceInterface $utilSanitizeService
     */
    public function __construct(ErrorLoggerInterface $errorLogger, ErrorRendererInterface $errorRenderer, UtilSanitizeServiceInterface $utilSanitizeService)
    {
        $this->errorLogger = $errorLogger;
        $this->errorRenderer = $errorRenderer;
        $this->utilSanitizeService = $utilSanitizeService;
    }

    /**
     * @param \Exception|\Throwable $exception
     * @param bool $exit
     *
     * @return void
     */
    public function handleException($exception, $exit = true)
    {
        try {
            $exception = $this->sanitizeExceptionMessage($exception);
            $this->errorLogger->log($exception);

            $this->sendHeader($exception);

            $this->cleanOutputBuffer();
            echo $this->errorRenderer->render($exception);
        } catch (Throwable $internalException) {
            $this->errorLogger->log($internalException);
        }

        if ($exit) {
            $this->sendExitCode();
        }
    }

    /**
     * @return void
     */
    public function handleFatal()
    {
        $error = $this->getLastError();
        if (!$error) {
            return;
        }

        $message = sprintf('FATAL ERROR - %s', $error['message']);
        $exception = new ErrorException($message, 0, (int)$error['type'], (string)$error['file'], (int)$error['line']);
        $this->handleException($exception);
    }

    /**
     * @return array<string, int|string>
     */
    protected function getLastError()
    {
        return (array)error_get_last();
    }

    /**
     * @param \Throwable $exception
     *
     * @return void
     */
    protected function sendHeader(Throwable $exception): void
    {
        if ($this->getStatusCodeFromException($exception) === Response::HTTP_NOT_FOUND) {
            $this->send404Header();

            return;
        }

        $this->send500Header();
    }

    /**
     * @param \Throwable $exception
     *
     * @return int
     */
    protected function getStatusCodeFromException(Throwable $exception): int
    {
        if (method_exists($exception, static::METHOD_NAME_GET_STATUS_CODE)) {
            /** @phpstan-ignore-next-line method.notFound */
            return $exception->getStatusCode();
        }

        return $exception->getCode();
    }

    /**
     * @return void
     */
    protected function send404Header(): void
    {
        if (!headers_sent()) {
            header(static::HEADER_404);
        }
    }

    /**
     * @return void
     */
    protected function send500Header()
    {
        if (!headers_sent()) {
            header(static::HEADER_500);
        }
    }

    /**
     * @return void
     */
    protected function cleanOutputBuffer()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
    }

    /**
     * @return void
     */
    protected function sendExitCode()
    {
        exit(static::EXIT_CODE_ERROR);
    }

    /**
     * @param \Throwable $exception
     *
     * @return \Throwable
     */
    protected function sanitizeExceptionMessage(Throwable $exception): Throwable
    {
        $sanitizedExceptionMessage = $this->utilSanitizeService->sanitizeString($exception->getMessage());
        $sanitizedExceptionMessage = $this->utilSanitizeService->escapeHtml($sanitizedExceptionMessage);
        $exception = $this->injectSanitizedMessageIntoException($exception, $sanitizedExceptionMessage);

        return $exception;
    }

    /**
     * @param \Throwable $exception
     * @param string $sanitizedExceptionMessage
     *
     * @return \Throwable
     */
    protected function injectSanitizedMessageIntoException(Throwable $exception, string $sanitizedExceptionMessage): Throwable
    {
        $exceptionMessageProperty = new ReflectionProperty($exception, 'message');
        $exceptionMessageProperty->setAccessible(true);
        $exceptionMessageProperty->setValue($exception, $sanitizedExceptionMessage);

        return $exception;
    }
}
