<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeatureTest\Zed\SspServiceManagement\Communication\Plugin\ProductManagement;

use Codeception\Test\Unit;
use SprykerFeature\Zed\SspServiceManagement\Communication\Form\ShipmentTypeProductConcreteForm;
use SprykerFeature\Zed\SspServiceManagement\Communication\Plugin\ProductManagement\ShipmentTypeProductConcreteFormExpanderPlugin;
use SprykerFeatureTest\Zed\SspServiceManagement\SspServiceManagementCommunicationTester;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Forms;

/**
 * @group SprykerFeatureTest
 * @group Zed
 * @group SspServiceManagement
 * @group Communication
 * @group Plugin
 * @group ProductManagement
 * @group ShipmentTypeProductConcreteFormExpanderPluginTest
 */
class ShipmentTypeProductConcreteFormExpanderPluginTest extends Unit
{
    /**
     * @var \SprykerFeatureTest\Zed\SspServiceManagement\SspServiceManagementCommunicationTester
     */
    protected SspServiceManagementCommunicationTester $tester;

    /**
     * @return void
     */
    public function testExpandShouldAddShipmentTypeFieldToForm(): void
    {
        // Arrange
        $formFactory = Forms::createFormFactory();
        $builder = $formFactory->createBuilder(FormType::class);

        // Act
        $expandedBuilder = (new ShipmentTypeProductConcreteFormExpanderPlugin())
            ->expand($builder, []);

        // Assert
        $this->assertTrue($expandedBuilder->has(ShipmentTypeProductConcreteForm::FIELD_SHIPMENT_TYPES));
    }

    /**
     * @return void
     */
    public function testExpandShouldConfigureShipmentTypeFieldAsMultipleSelect(): void
    {
        // Arrange
        $formFactory = Forms::createFormFactory();
        $builder = $formFactory->createBuilder(FormType::class);

        // Act
        $expandedBuilder = (new ShipmentTypeProductConcreteFormExpanderPlugin())
            ->expand($builder, []);
        $form = $expandedBuilder->getForm();

        // Assert
        $this->assertTrue($form->get(ShipmentTypeProductConcreteForm::FIELD_SHIPMENT_TYPES)->getConfig()->getOption('multiple'));
    }

    /**
     * @return void
     */
    public function testExpandShouldSetShipmentTypeFieldAsNotRequired(): void
    {
        // Arrange
        $formFactory = Forms::createFormFactory();
        $builder = $formFactory->createBuilder(FormType::class);

        // Act
        $expandedBuilder = (new ShipmentTypeProductConcreteFormExpanderPlugin())
            ->expand($builder, []);
        $form = $expandedBuilder->getForm();

        // Assert
        $this->assertFalse($form->get(ShipmentTypeProductConcreteForm::FIELD_SHIPMENT_TYPES)->getConfig()->getOption('required'));
    }

    /**
     * @return void
     */
    public function testExpandShouldPreserveExistingFormFields(): void
    {
        // Arrange
        $formFactory = Forms::createFormFactory();
        $builder = $formFactory->createBuilder(FormType::class);
        $builder->add('existingField', FormType::class);

        // Act
        $expandedBuilder = (new ShipmentTypeProductConcreteFormExpanderPlugin())
            ->expand($builder, []);

        // Assert
        $this->assertTrue($expandedBuilder->has('existingField'));
        $this->assertTrue($expandedBuilder->has(ShipmentTypeProductConcreteForm::FIELD_SHIPMENT_TYPES));
    }
}
