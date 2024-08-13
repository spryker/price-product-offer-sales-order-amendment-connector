<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Payment\Business\Authorizer;

use Generated\Shared\Transfer\CheckoutErrorTransfer;
use Generated\Shared\Transfer\CheckoutResponseTransfer;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\PaymentAuthorizeRequestTransfer;
use Generated\Shared\Transfer\PaymentAuthorizeResponseTransfer;
use Generated\Shared\Transfer\PaymentMethodTransfer;
use Generated\Shared\Transfer\PaymentTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\SaveOrderTransfer;
use Spryker\Client\Payment\PaymentClientInterface;
use Spryker\Service\Payment\PaymentServiceInterface;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Payment\Business\Exception\AuthorizationEndpointNotFoundException;
use Spryker\Zed\Payment\Business\Mapper\QuoteDataMapperInterface;
use Spryker\Zed\Payment\Dependency\Facade\PaymentToLocaleFacadeInterface;
use Spryker\Zed\Payment\Dependency\Facade\PaymentToStoreFacadeInterface;
use Spryker\Zed\Payment\PaymentConfig;
use Spryker\Zed\Payment\Persistence\PaymentRepositoryInterface;

class ForeignPaymentAuthorizer implements ForeignPaymentAuthorizerInterface
{
    /**
     * @var string
     */
    protected const ERROR_CODE_PAYMENT_FAILED = 'payment failed';

    /**
     * @var \Spryker\Zed\Payment\Business\Mapper\QuoteDataMapperInterface
     */
    protected $quoteDataMapper;

    /**
     * @var \Spryker\Zed\Payment\Dependency\Facade\PaymentToLocaleFacadeInterface
     */
    protected $localeFacade;

    /**
     * @var \Spryker\Zed\Payment\Persistence\PaymentRepositoryInterface
     */
    protected $paymentRepository;

    /**
     * @var \Spryker\Client\Payment\PaymentClientInterface
     */
    protected $paymentClient;

    /**
     * @var \Spryker\Zed\Payment\PaymentConfig
     */
    protected $paymentConfig;

    /**
     * @var \Spryker\Zed\Payment\Dependency\Facade\PaymentToStoreFacadeInterface
     */
    protected $storeFacade;

    /**
     * @var \Spryker\Service\Payment\PaymentServiceInterface
     */
    protected $paymentService;

    /**
     * @var array<int, \Spryker\Zed\PaymentExtension\Dependency\Plugin\PaymentAuthorizeRequestExpanderPluginInterface>
     */
    protected $paymentAuthorizeRequestExpanderPlugins;

    /**
     * @param \Spryker\Zed\Payment\Business\Mapper\QuoteDataMapperInterface $quoteDataMapper
     * @param \Spryker\Zed\Payment\Dependency\Facade\PaymentToLocaleFacadeInterface $localeFacade
     * @param \Spryker\Zed\Payment\Persistence\PaymentRepositoryInterface $paymentRepository
     * @param \Spryker\Client\Payment\PaymentClientInterface $paymentClient
     * @param \Spryker\Zed\Payment\PaymentConfig $paymentConfig
     * @param \Spryker\Zed\Payment\Dependency\Facade\PaymentToStoreFacadeInterface $storeFacade
     * @param \Spryker\Service\Payment\PaymentServiceInterface $paymentService
     * @param array<int, \Spryker\Zed\PaymentExtension\Dependency\Plugin\PaymentAuthorizeRequestExpanderPluginInterface> $paymentAuthorizeRequestExpanderPlugins
     */
    public function __construct(
        QuoteDataMapperInterface $quoteDataMapper,
        PaymentToLocaleFacadeInterface $localeFacade,
        PaymentRepositoryInterface $paymentRepository,
        PaymentClientInterface $paymentClient,
        PaymentConfig $paymentConfig,
        PaymentToStoreFacadeInterface $storeFacade,
        PaymentServiceInterface $paymentService,
        array $paymentAuthorizeRequestExpanderPlugins
    ) {
        $this->quoteDataMapper = $quoteDataMapper;
        $this->localeFacade = $localeFacade;
        $this->paymentRepository = $paymentRepository;
        $this->paymentClient = $paymentClient;
        $this->paymentConfig = $paymentConfig;
        $this->storeFacade = $storeFacade;
        $this->paymentService = $paymentService;
        $this->paymentAuthorizeRequestExpanderPlugins = $paymentAuthorizeRequestExpanderPlugins;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponseTransfer
     *
     * @return void
     */
    public function initForeignPaymentForCheckoutProcess(
        QuoteTransfer $quoteTransfer,
        CheckoutResponseTransfer $checkoutResponseTransfer
    ): void {
        $paymentSelectionKey = $this->paymentService->getPaymentSelectionKey($quoteTransfer->getPaymentOrFail());

        if ($paymentSelectionKey !== PaymentTransfer::FOREIGN_PAYMENTS) {
            return;
        }

        $paymentMethodKey = $this->paymentService->getPaymentMethodKey($quoteTransfer->getPaymentOrFail());
        $paymentMethodTransfer = $this->paymentRepository->findPaymentMethod(
            (new PaymentMethodTransfer())->setPaymentMethodKey($paymentMethodKey),
        );

        if (!$paymentMethodTransfer || (!$paymentMethodTransfer->getPaymentAuthorizationEndpoint() && !$paymentMethodTransfer->getPaymentMethodAppConfiguration())) {
            return;
        }

        $paymentAuthorizeResponseTransfer = $this->requestPaymentAuthorization(
            $paymentMethodTransfer,
            $quoteTransfer,
            $checkoutResponseTransfer->getSaveOrderOrFail(),
        );
        $this->processPaymentAuthorizeResponse(
            $paymentAuthorizeResponseTransfer,
            $checkoutResponseTransfer,
        );
    }

    /**
     * @param \Generated\Shared\Transfer\PaymentMethodTransfer $paymentMethodTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     * @param \Generated\Shared\Transfer\SaveOrderTransfer $saveOrderTransfer
     *
     * @return \Generated\Shared\Transfer\PaymentAuthorizeResponseTransfer
     */
    protected function requestPaymentAuthorization(
        PaymentMethodTransfer $paymentMethodTransfer,
        QuoteTransfer $quoteTransfer,
        SaveOrderTransfer $saveOrderTransfer
    ): PaymentAuthorizeResponseTransfer {
        $localeTransfer = $this->localeFacade->getCurrentLocale();
        $quoteTransfer->setOrderReference($saveOrderTransfer->getOrderReference());
        $quoteTransfer->getCustomerOrFail()->setLocale($localeTransfer);

        $language = $this->getCurrentLanguage($localeTransfer);
        $postData = [
            'orderData' => $this->quoteDataMapper->mapQuoteDataByAllowedFields(
                $quoteTransfer,
                $this->paymentConfig->getQuoteFieldsForForeignPayment(),
            ),
            'redirectSuccessUrl' => $this->generatePaymentRedirectUrl(
                $language,
                $this->paymentConfig->getSuccessRoute(),
            ),
            'redirectCancelUrl' => $this->generatePaymentRedirectUrl(
                $language,
                $this->paymentConfig->getCancelRoute(),
                ['orderReference' => $quoteTransfer->getOrderReference()],
            ),
            'checkoutSummaryPageUrl' => $this->generatePaymentRedirectUrl(
                $language,
                $this->paymentConfig->getCheckoutSummaryPageRoute(),
            ),
        ];

        $authorizationEndpoint = $this->getAuthorizationEndpoint($paymentMethodTransfer);

        $paymentAuthorizeRequestTransfer = (new PaymentAuthorizeRequestTransfer())
            ->setRequestUrl($authorizationEndpoint)
            ->setStoreReference($this->findCurrentStoreReference($quoteTransfer))
            ->setTenantIdentifier($this->paymentConfig->getTenantIdentifier())
            ->setPostData($postData);

        $paymentAuthorizeRequestTransfer = $this->executePaymentAuthorizeRequestExpanderPlugins($paymentAuthorizeRequestTransfer);

        return $this->paymentClient->authorizeForeignPayment($paymentAuthorizeRequestTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\PaymentMethodTransfer $paymentMethodTransfer
     *
     * @throws \Spryker\Zed\Payment\Business\Exception\AuthorizationEndpointNotFoundException
     *
     * @return string
     */
    protected function getAuthorizationEndpoint(PaymentMethodTransfer $paymentMethodTransfer): string
    {
        $paymentMethodAppConfigurationTransfer = $paymentMethodTransfer->getPaymentMethodAppConfiguration();

        if (!$paymentMethodAppConfigurationTransfer) {
            return $paymentMethodTransfer->getPaymentAuthorizationEndpoint();
        }

        foreach ($paymentMethodAppConfigurationTransfer->getEndpoints() as $endpointTransfer) {
            if ($endpointTransfer->getNameOrFail() === 'authorization') {
                return sprintf('%s%s', $paymentMethodAppConfigurationTransfer->getBaseUrlOrFail(), $endpointTransfer->getPathOrFail());
            }
        }

        throw new AuthorizationEndpointNotFoundException(sprintf('Could not find an authorization endpoint for payment method "%s"', $paymentMethodTransfer->getPaymentMethodKey()));
    }

    /**
     * @param string $language
     * @param string $urlPath
     * @param array<string, mixed> $queryParts
     *
     * @return string
     */
    protected function generatePaymentRedirectUrl(string $language, string $urlPath, array $queryParts = []): string
    {
        if ($this->isAbsoluteUrl($urlPath)) {
            return $this->addQueryParametersToUrl($urlPath, $queryParts);
        }

        $url = sprintf(
            '%s/%s%s',
            $this->paymentConfig->getBaseUrlYves(),
            $language,
            $urlPath,
        );

        return Url::generate($url, $queryParts)->build();
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    protected function isAbsoluteUrl(string $url): bool
    {
        $urlComponents = parse_url($url);

        return isset($urlComponents['host']);
    }

    /**
     * @param string $url
     * @param array<string, mixed> $queryParams
     *
     * @return string
     */
    protected function addQueryParametersToUrl(string $url, array $queryParams): string
    {
        if ($queryParams === []) {
            return $url;
        }

        $urlComponents = parse_url($url);

        $url .= isset($urlComponents['query']) ? '&' : '?';
        $url .= http_build_query($queryParams);

        return $url;
    }

    /**
     * @param \Generated\Shared\Transfer\LocaleTransfer $localeTransfer
     *
     * @return string
     */
    protected function getCurrentLanguage(LocaleTransfer $localeTransfer): string
    {
        $splitLocale = explode('_', $localeTransfer->getLocaleNameOrFail());

        return $splitLocale[0];
    }

    /**
     * @param \Generated\Shared\Transfer\PaymentAuthorizeResponseTransfer $paymentAuthorizeResponseTransfer
     * @param \Generated\Shared\Transfer\CheckoutResponseTransfer $checkoutResponseTransfer
     *
     * @return void
     */
    protected function processPaymentAuthorizeResponse(
        PaymentAuthorizeResponseTransfer $paymentAuthorizeResponseTransfer,
        CheckoutResponseTransfer $checkoutResponseTransfer
    ): void {
        if (!$paymentAuthorizeResponseTransfer->getIsSuccessful()) {
            $checkoutErrorTransfer = (new CheckoutErrorTransfer())
                ->setErrorCode(static::ERROR_CODE_PAYMENT_FAILED)
                ->setMessage($paymentAuthorizeResponseTransfer->getMessage());
            $checkoutResponseTransfer->setIsSuccess(false)
                ->addError($checkoutErrorTransfer);

            return;
        }

        if ($this->paymentConfig->getStoreFrontPaymentPage() === '') {
            $checkoutResponseTransfer
                ->setIsExternalRedirect(true)
                ->setRedirectUrl($paymentAuthorizeResponseTransfer->getRedirectUrl());

            return;
        }

        $redirectUrl = $this->addQueryParametersToUrl($this->paymentConfig->getStoreFrontPaymentPage(), [
            'url' => base64_encode($paymentAuthorizeResponseTransfer->getRedirectUrl()),
        ]);

        $checkoutResponseTransfer
            ->setIsExternalRedirect(true)
            ->setRedirectUrl($redirectUrl);
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return string|null
     */
    protected function findCurrentStoreReference(QuoteTransfer $quoteTransfer): ?string
    {
        return $this->storeFacade
            ->getStoreByName($quoteTransfer->getStoreOrFail()->getNameOrFail())
            ->getStoreReference();
    }

    /**
     * @param \Generated\Shared\Transfer\PaymentAuthorizeRequestTransfer $paymentAuthorizeRequestTransfer
     *
     * @return \Generated\Shared\Transfer\PaymentAuthorizeRequestTransfer
     */
    protected function executePaymentAuthorizeRequestExpanderPlugins(
        PaymentAuthorizeRequestTransfer $paymentAuthorizeRequestTransfer
    ): PaymentAuthorizeRequestTransfer {
        foreach ($this->paymentAuthorizeRequestExpanderPlugins as $paymentAuthorizeRequestExpanderPlugin) {
            $paymentAuthorizeRequestTransfer = $paymentAuthorizeRequestExpanderPlugin->expand($paymentAuthorizeRequestTransfer);
        }

        return $paymentAuthorizeRequestTransfer;
    }
}
