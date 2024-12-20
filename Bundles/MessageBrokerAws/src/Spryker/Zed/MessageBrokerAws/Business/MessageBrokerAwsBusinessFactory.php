<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MessageBrokerAws\Business;

use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;
use GuzzleHttp\ClientInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\MessageBrokerAws\Business\Config\ConfigFormatterInterface;
use Spryker\Zed\MessageBrokerAws\Business\Config\JsonToArrayConfigFormatter;
use Spryker\Zed\MessageBrokerAws\Business\MessageDataFilter\IdFieldsMessageDataFilter;
use Spryker\Zed\MessageBrokerAws\Business\MessageDataFilter\MessageDataFilterConfigurator;
use Spryker\Zed\MessageBrokerAws\Business\MessageDataFilter\MessageDataFilterInterface;
use Spryker\Zed\MessageBrokerAws\Business\MessageDataFilter\NullFieldsMessageDataFilter;
use Spryker\Zed\MessageBrokerAws\Business\Normalizer\TransferNormalizer;
use Spryker\Zed\MessageBrokerAws\Business\Queue\AwsSqsQueuesCreator;
use Spryker\Zed\MessageBrokerAws\Business\Queue\AwsSqsQueuesCreatorInterface;
use Spryker\Zed\MessageBrokerAws\Business\Queue\AwsSqsQueuesSubscriber;
use Spryker\Zed\MessageBrokerAws\Business\Queue\AwsSqsQueuesSubscriberInterface;
use Spryker\Zed\MessageBrokerAws\Business\Receiver\Client\HttpChannelReceiverClient;
use Spryker\Zed\MessageBrokerAws\Business\Receiver\Client\Locator\ReceiverClientLocator;
use Spryker\Zed\MessageBrokerAws\Business\Receiver\Client\Locator\ReceiverClientLocatorInterface;
use Spryker\Zed\MessageBrokerAws\Business\Receiver\Client\ReceiverClientInterface;
use Spryker\Zed\MessageBrokerAws\Business\Receiver\Client\SqsReceiverClient;
use Spryker\Zed\MessageBrokerAws\Business\Receiver\Receiver;
use Spryker\Zed\MessageBrokerAws\Business\Receiver\ReceiverInterface;
use Spryker\Zed\MessageBrokerAws\Business\Sender\Client\Formatter\CustomHttpHeaderFormatter;
use Spryker\Zed\MessageBrokerAws\Business\Sender\Client\Formatter\HttpHeaderFormatter;
use Spryker\Zed\MessageBrokerAws\Business\Sender\Client\Formatter\HttpHeaderFormatterInterface;
use Spryker\Zed\MessageBrokerAws\Business\Sender\Client\HttpChannelSenderClient;
use Spryker\Zed\MessageBrokerAws\Business\Sender\Client\HttpSenderClient;
use Spryker\Zed\MessageBrokerAws\Business\Sender\Client\Locator\SenderClientLocator;
use Spryker\Zed\MessageBrokerAws\Business\Sender\Client\Locator\SenderClientLocatorInterface;
use Spryker\Zed\MessageBrokerAws\Business\Sender\Client\SenderClientInterface;
use Spryker\Zed\MessageBrokerAws\Business\Sender\Client\SnsSenderClient;
use Spryker\Zed\MessageBrokerAws\Business\Sender\Client\SqsSenderClient;
use Spryker\Zed\MessageBrokerAws\Business\Sender\Sender;
use Spryker\Zed\MessageBrokerAws\Business\Sender\SenderInterface;
use Spryker\Zed\MessageBrokerAws\Business\Serializer\TransferSerializer;
use Spryker\Zed\MessageBrokerAws\Business\Sns\AwsSnsTopicCreator;
use Spryker\Zed\MessageBrokerAws\Business\Sns\AwsSnsTopicCreatorInterface;
use Spryker\Zed\MessageBrokerAws\Dependency\Facade\MessageBrokerAwsToStoreFacadeInterface;
use Spryker\Zed\MessageBrokerAws\Dependency\Service\MessageBrokerAwsToUtilEncodingServiceInterface;
use Spryker\Zed\MessageBrokerAws\MessageBrokerAwsConfig;
use Spryker\Zed\MessageBrokerAws\MessageBrokerAwsDependencyProvider;
use Symfony\Component\Messenger\Transport\Serialization\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

/**
 * @method \Spryker\Zed\MessageBrokerAws\MessageBrokerAwsConfig getConfig()
 */
class MessageBrokerAwsBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @deprecated Will be removed without replacement.
     *
     * @return \Spryker\Zed\MessageBrokerAws\Business\Sender\SenderInterface
     */
    public function createSender(): SenderInterface
    {
        return new Sender(
            $this->getConfig(),
            $this->createSenderClientLocator(),
            $this->createConfigFormatter(),
        );
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return \Spryker\Zed\MessageBrokerAws\Business\Sender\Client\Locator\SenderClientLocatorInterface
     */
    public function createSenderClientLocator(): SenderClientLocatorInterface
    {
        return new SenderClientLocator(
            $this->getConfig(),
            $this->getSenderClients(),
            $this->createConfigFormatter(),
        );
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return array<string, \Spryker\Zed\MessageBrokerAws\Business\Sender\Client\SenderClientInterface>
     */
    public function getSenderClients(): array
    {
        return [
            MessageBrokerAwsConfig::SNS_TRANSPORT => $this->createSnsSenderClient(),
            MessageBrokerAwsConfig::SQS_TRANSPORT => $this->createSqsSenderClient(),
            MessageBrokerAwsConfig::HTTP_TRANSPORT => $this->createHttpSenderClient(),
        ];
    }

    /**
     * @deprecated Use {@link \Spryker\Zed\MessageBrokerAws\Business\MessageBrokerAwsBusinessFactory::createHttpChannelSenderClient()} instead.
     *
     * @return \Spryker\Zed\MessageBrokerAws\Business\Sender\Client\SenderClientInterface
     */
    public function createSnsSenderClient(): SenderClientInterface
    {
        return new SnsSenderClient(
            $this->getConfig(),
            $this->createSerializer(),
            $this->createConfigFormatter(),
        );
    }

    /**
     * @deprecated Use {@link \Spryker\Zed\MessageBrokerAws\Business\MessageBrokerAwsBusinessFactory::createHttpChannelSenderClient()} instead.
     *
     * @return \Spryker\Zed\MessageBrokerAws\Business\Sender\Client\SenderClientInterface
     */
    public function createSqsSenderClient(): SenderClientInterface
    {
        return new SqsSenderClient(
            $this->getConfig(),
            $this->createSerializer(),
            $this->createConfigFormatter(),
        );
    }

    /**
     * @deprecated Use {@link \Spryker\Zed\MessageBrokerAws\Business\MessageBrokerAwsBusinessFactory::createHttpChannelSenderClient()} instead.
     *
     * @return \Spryker\Zed\MessageBrokerAws\Business\Sender\Client\SenderClientInterface
     */
    public function createHttpSenderClient(): SenderClientInterface
    {
        return new HttpSenderClient(
            $this->getConfig(),
            $this->createSerializer(),
            $this->createConfigFormatter(),
            $this->createCustomHttpHeaderFormatter(),
        );
    }

    /**
     * @return \Spryker\Zed\MessageBrokerAws\Business\Sender\Client\SenderClientInterface
     */
    public function createHttpChannelSenderClient(): SenderClientInterface
    {
        return new HttpChannelSenderClient(
            $this->getConfig(),
            $this->createSerializer(),
            $this->createHttpHeaderFormatter(),
            $this->getHttpClient(),
            $this->getUtilEncodingService(),
        );
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return \Spryker\Zed\MessageBrokerAws\Business\Receiver\ReceiverInterface
     */
    public function createReceiver(): ReceiverInterface
    {
        return new Receiver(
            $this->getConfig(),
            $this->createReceiverClientLocator(),
        );
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return \Spryker\Zed\MessageBrokerAws\Business\Receiver\Client\Locator\ReceiverClientLocatorInterface
     */
    public function createReceiverClientLocator(): ReceiverClientLocatorInterface
    {
        return new ReceiverClientLocator(
            $this->getConfig(),
            $this->getReceiverClients(),
            $this->createConfigFormatter(),
        );
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return array<string, \Spryker\Zed\MessageBrokerAws\Business\Receiver\Client\ReceiverClientInterface>
     */
    public function getReceiverClients(): array
    {
        return [
            MessageBrokerAwsConfig::SQS_TRANSPORT => $this->createSqsReceiverClient(),
        ];
    }

    /**
     * @return \Spryker\Zed\MessageBrokerAws\Business\Receiver\Client\ReceiverClientInterface
     */
    public function createHttpChannelReceiverClient(): ReceiverClientInterface
    {
        return new HttpChannelReceiverClient(
            $this->getConfig(),
            $this->createSerializer(),
            $this->getHttpChannelMessageReceiverRequestExpanderPlugins(),
            $this->getHttpClient(),
            $this->getUtilEncodingService(),
        );
    }

    /**
     * @deprecated Use {@link \Spryker\Zed\MessageBrokerAws\Business\MessageBrokerAwsBusinessFactory::createHttpChannelReceiverClient()} instead.
     *
     * @return \Spryker\Zed\MessageBrokerAws\Business\Receiver\Client\ReceiverClientInterface
     */
    public function createSqsReceiverClient(): ReceiverClientInterface
    {
        return new SqsReceiverClient(
            $this->getConfig(),
            $this->createSerializer(),
            $this->createConfigFormatter(),
        );
    }

    /**
     * @return \Symfony\Component\Messenger\Transport\Serialization\SerializerInterface
     */
    public function createSerializer(): SerializerInterface
    {
        return new TransferSerializer(
            $this->createSymfonySerializer(),
            $this->getUtilEncodingService(),
            $this->getMessageDataFilters(),
        );
    }

    /**
     * @return \Symfony\Component\Serializer\Serializer
     */
    public function createSymfonySerializer(): SymfonySerializer
    {
        return new SymfonySerializer(
            $this->getSerializerNormalizer(),
            $this->getSerializerEncoders(),
        );
    }

    /**
     * @return array<(\Symfony\Component\Serializer\Normalizer\NormalizerInterface|\Symfony\Component\Serializer\Normalizer\DenormalizerInterface)>
     */
    public function getSerializerNormalizer(): array
    {
        return [
            $this->createArrayDenormalizer(),
            $this->createTransferNormalizer(),
        ];
    }

    /**
     * @return \Symfony\Component\Serializer\Normalizer\ArrayDenormalizer
     */
    public function createArrayDenormalizer(): ArrayDenormalizer
    {
        return new ArrayDenormalizer();
    }

    /**
     * @return \Symfony\Component\Serializer\Normalizer\NormalizerInterface
     */
    public function createTransferNormalizer(): NormalizerInterface
    {
        return new TransferNormalizer();
    }

    /**
     * @return array<(\Symfony\Component\Serializer\Encoder\EncoderInterface|\Symfony\Component\Serializer\Encoder\DecoderInterface)>
     */
    public function getSerializerEncoders(): array
    {
        return [
            $this->createJsonEncoder(),
        ];
    }

    /**
     * @return \Symfony\Component\Serializer\Encoder\JsonEncoder
     */
    public function createJsonEncoder(): JsonEncoder
    {
        return new JsonEncoder();
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return \Spryker\Zed\MessageBrokerAws\Business\Config\ConfigFormatterInterface
     */
    public function createConfigFormatter(): ConfigFormatterInterface
    {
        return new JsonToArrayConfigFormatter(
            $this->getStoreFacade(),
            $this->getUtilEncodingService(),
        );
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return \Spryker\Zed\MessageBrokerAws\Business\Queue\AwsSqsQueuesCreatorInterface
     */
    public function createAwsSqsQueuesCreator(): AwsSqsQueuesCreatorInterface
    {
        return new AwsSqsQueuesCreator(
            $this->getAwsSqsClient(),
            $this->getConfig(),
        );
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return \Spryker\Zed\MessageBrokerAws\Business\Sns\AwsSnsTopicCreatorInterface
     */
    public function createAwsSnsTopicCreator(): AwsSnsTopicCreatorInterface
    {
        return new AwsSnsTopicCreator(
            $this->getAwsSnsClient(),
            $this->getConfig(),
        );
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return \Spryker\Zed\MessageBrokerAws\Business\Queue\AwsSqsQueuesSubscriberInterface
     */
    public function createAwsSqsQueueSubscriber(): AwsSqsQueuesSubscriberInterface
    {
        return new AwsSqsQueuesSubscriber(
            $this->getAwsSnsClient(),
            $this->getConfig(),
        );
    }

    /**
     * @return \GuzzleHttp\ClientInterface
     */
    public function getHttpClient(): ClientInterface
    {
        return $this->getProvidedDependency(MessageBrokerAwsDependencyProvider::CLIENT_HTTP);
    }

    /**
     * @return list<\Spryker\Zed\MessageBrokerAwsExtension\Dependency\Plugin\HttpChannelMessageReceiverRequestExpanderPluginInterface>
     */
    public function getHttpChannelMessageReceiverRequestExpanderPlugins(): array
    {
        return $this->getProvidedDependency(MessageBrokerAwsDependencyProvider::PLUGINS_HTTP_CHANNEL_MESSAGE_RECEIVER_REQUEST_EXPANDER);
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return \Aws\Sqs\SqsClient
     */
    public function getAwsSqsClient(): SqsClient
    {
        return $this->getProvidedDependency(MessageBrokerAwsDependencyProvider::CLIENT_AWS_SQS);
    }

    /**
     * @return \Aws\Sns\SnsClient
     */
    public function getAwsSnsClient(): SnsClient
    {
        return $this->getProvidedDependency(MessageBrokerAwsDependencyProvider::CLIENT_AWS_SNS);
    }

    /**
     * @return \Spryker\Zed\MessageBrokerAws\Business\Sender\Client\Formatter\HttpHeaderFormatterInterface
     */
    public function createHttpHeaderFormatter(): HttpHeaderFormatterInterface
    {
        return new HttpHeaderFormatter($this->getConfig());
    }

    /**
     * @deprecated Use {@link \Spryker\Zed\MessageBrokerAws\Business\MessageBrokerAwsBusinessFactory::createHttpHeaderFormatter()} instead.
     *
     * @return \Spryker\Zed\MessageBrokerAws\Business\Sender\Client\Formatter\HttpHeaderFormatterInterface
     */
    public function createCustomHttpHeaderFormatter(): HttpHeaderFormatterInterface
    {
        return new CustomHttpHeaderFormatter($this->getConfig());
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return \Spryker\Zed\MessageBrokerAws\Dependency\Facade\MessageBrokerAwsToStoreFacadeInterface
     */
    public function getStoreFacade(): MessageBrokerAwsToStoreFacadeInterface
    {
        return $this->getProvidedDependency(MessageBrokerAwsDependencyProvider::FACADE_STORE);
    }

    /**
     * @return \Spryker\Zed\MessageBrokerAws\Dependency\Service\MessageBrokerAwsToUtilEncodingServiceInterface
     */
    public function getUtilEncodingService(): MessageBrokerAwsToUtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(MessageBrokerAwsDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    /**
     * @return array<\Spryker\Zed\MessageBrokerAws\Business\MessageDataFilter\MessageDataFilterInterface>
     */
    public function getMessageDataFilters(): array
    {
        return [
            $this->createStripIdFieldsMessageDataFilter(),
            $this->createStripNullFieldsMessageDataFilter(),
        ];
    }

    /**
     * @return \Spryker\Zed\MessageBrokerAws\Business\MessageDataFilter\MessageDataFilterInterface
     */
    public function createStripIdFieldsMessageDataFilter(): MessageDataFilterInterface
    {
        return new IdFieldsMessageDataFilter(
            $this->createMessageDataFilterConfiguration(),
        );
    }

    /**
     * @return \Spryker\Zed\MessageBrokerAws\Business\MessageDataFilter\MessageDataFilterInterface
     */
    public function createStripNullFieldsMessageDataFilter(): MessageDataFilterInterface
    {
        return new NullFieldsMessageDataFilter(
            $this->createMessageDataFilterConfiguration(),
        );
    }

    /**
     * @return \Spryker\Zed\MessageBrokerAws\Business\MessageDataFilter\MessageDataFilterConfigurator
     */
    public function createMessageDataFilterConfiguration(): MessageDataFilterConfigurator
    {
        return new MessageDataFilterConfigurator(
            $this->getConfig()->getDefaultMessageDataFilterConfiguration(),
        );
    }
}
