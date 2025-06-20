<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\ConfigurableBundleStorage\Communication\Plugin\Event\Listener;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\EventEntityTransfer;
use Spryker\Zed\ConfigurableBundle\Dependency\ConfigurableBundleEvents;
use Spryker\Zed\ConfigurableBundleStorage\Communication\Plugin\Event\Listener\ConfigurableBundleTemplateStoragePublishListener;
use Spryker\Zed\ConfigurableBundleStorage\Communication\Plugin\Event\Listener\ConfigurableBundleTemplateStorageUnpublishListener;
use Spryker\Zed\ConfigurableBundleStorage\Persistence\ConfigurableBundleStorageRepository;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group ConfigurableBundleStorage
 * @group Communication
 * @group Plugin
 * @group Event
 * @group Listener
 * @group ConfigurableBundleTemplateStorageUnpublishListenerTest
 * Add your own group annotations below this line
 */
class ConfigurableBundleTemplateStorageUnpublishListenerTest extends Unit
{
    /**
     * @var \SprykerTest\Zed\ConfigurableBundleStorage\ConfigurableBundleStorageCommunicationTester
     */
    protected $tester;

    /**
     * @var \Spryker\Zed\ConfigurableBundleStorage\Persistence\ConfigurableBundleStorageRepositoryInterface
     */
    protected $configurableBundleStorageRepository;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->configurableBundleStorageRepository = new ConfigurableBundleStorageRepository();
    }

    /**
     * @return void
     */
    public function testConfigurableBundleTemplateStorageUnpublishListenerCanBeUnpublished(): void
    {
        // Arrange
        $configurableBundleTemplateTransfer = $this->tester->createActiveConfigurableBundleTemplate();

        $configurableBundleTemplateStoragePublishListener = (new ConfigurableBundleTemplateStoragePublishListener())
            ->setFacade($this->tester->getFacade());

        $configurableBundleTemplateStorageUnpublishListener = (new ConfigurableBundleTemplateStorageUnpublishListener())
            ->setFacade($this->tester->getFacade());

        $eventEntityTransfers = [
            (new EventEntityTransfer())->setId($configurableBundleTemplateTransfer->getIdConfigurableBundleTemplate()),
        ];

        // Act
        $configurableBundleTemplateStoragePublishListener->handleBulk($eventEntityTransfers, ConfigurableBundleEvents::CONFIGURABLE_BUNDLE_TEMPLATE_PUBLISH);
        $configurableBundleTemplateStorageUnpublishListener->handleBulk($eventEntityTransfers, ConfigurableBundleEvents::CONFIGURABLE_BUNDLE_TEMPLATE_UNPUBLISH);

        $configurableBundleTemplateStorageEntities = $this->configurableBundleStorageRepository
            ->getConfigurableBundleTemplateStorageEntityMap([$configurableBundleTemplateTransfer->getIdConfigurableBundleTemplate()]);

        // Assert
        $this->assertEmpty($configurableBundleTemplateStorageEntities);
    }
}
