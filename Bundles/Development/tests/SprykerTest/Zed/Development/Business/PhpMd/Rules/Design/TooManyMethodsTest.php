<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Development\Business\PhpMd\Rules\Design;

use Codeception\Test\Unit;
use PHPMD\AbstractNode;
use PHPMD\Node\AbstractTypeNode;
use Spryker\Zed\Development\Business\PhpMd\Rules\Design\TooManyMethods;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Development
 * @group Business
 * @group PhpMd
 * @group Rules
 * @group Design
 * @group TooManyMethodsTest
 * Add your own group annotations below this line
 */
class TooManyMethodsTest extends Unit
{
    /**
     * @var int
     */
    public const THRESHOLD_LOW = 1;

    /**
     * @var int
     */
    public const THRESHOLD_HIGH = 3;

    /**
     * @var int
     */
    public const NUMBER_OF_METHODS = 2;

    /**
     * @dataProvider ignorableNodesProvider
     *
     * @param string $fullyQualifiedClassName
     * @param string $nodeName
     *
     * @return void
     */
    public function testApplyDoesNotAddViolationIfNodeIsIgnorable(string $fullyQualifiedClassName, string $nodeName): void
    {
        $nodeMock = $this->getNodeMock($fullyQualifiedClassName, $nodeName);

        $tooManyMethodsMock = $this->getTooManyMethodsMock();
        $tooManyMethodsMock->expects($this->once())->method('getIntProperty')->willReturn(static::THRESHOLD_LOW);
        $tooManyMethodsMock->expects($this->never())->method('addViolation');
        $tooManyMethodsMock->apply($nodeMock);
    }

    /**
     * @return array
     */
    public function ignorableNodesProvider(): array
    {
        return [
            ['Client\\Foo\\BarFacade', 'BarFacade'],
            ['Yves\\Foo\\BarFacade', 'BarFacade'],
            ['Zed\\Foo\\BarFacade', 'BarFacade'],
            ['Zed\\Foo\\FooBusinessFactory', 'FooBusinessFactory'],
            ['Zed\\Foo\\FooCommunicationFactory', 'BarCommunicationFactory'],
            ['Zed\\Foo\\Factory', 'BarCommunicationFactory'],
        ];
    }

    /**
     * @return void
     */
    public function testApplyAddsViolationWhenClassIsNotIgnorable(): void
    {
        $nodeMock = $this->getNodeMock('Foo', 'Bar');

        $tooManyMethodsMock = $this->getTooManyMethodsMock();
        $tooManyMethodsMock->expects($this->once())->method('getIntProperty')->willReturn(static::THRESHOLD_LOW);
        $tooManyMethodsMock->expects($this->once())->method('addViolation');
        $tooManyMethodsMock->apply($nodeMock);
    }

    /**
     * @return void
     */
    public function testApplyDoesNotAddViolationIfNumberOfMethodsLowerThenThreshold(): void
    {
        $nodeMock = $this->getNodeMock('Foo', 'Bar');

        $tooManyMethodsMock = $this->getTooManyMethodsMock();
        $tooManyMethodsMock->expects($this->once())->method('getIntProperty')->willReturn(static::THRESHOLD_HIGH);
        $tooManyMethodsMock->expects($this->never())->method('addViolation');
        $tooManyMethodsMock->apply($nodeMock);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Development\Business\PhpMd\Rules\Design\TooManyMethods
     */
    protected function getTooManyMethodsMock(): TooManyMethods
    {
        $mockBuilder = $this->getMockBuilder(TooManyMethods::class);
        $mockBuilder->onlyMethods(['addViolation', 'getIntProperty', 'getStringProperty']);

        $tooManyMethodsMock = $mockBuilder->getMock();
        $tooManyMethodsMock->expects($this->any())->method('getStringProperty')->willReturn('/ignore regex pattern/');

        return $tooManyMethodsMock;
    }

    /**
     * @param string $fullyQualifiedClassName
     * @param string $nodeName
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\PHPMD\AbstractNode
     */
    protected function getNodeMock(string $fullyQualifiedClassName, string $nodeName): AbstractNode
    {
        $mockBuilder = $this->getMockBuilder(AbstractTypeNode::class);
        $mockBuilder->addMethods(['getNamespace'])
            ->onlyMethods(['getMetric', 'getName', 'getNamespaceName', 'hasSuppressWarningsAnnotationFor', 'getFullQualifiedName', 'getParentName', 'getMethodNames'])
            ->disableOriginalConstructor();

        $nodeMock = $mockBuilder->getMock();
        $nodeMock->expects($this->once())->method('getMetric')->willReturn(static::NUMBER_OF_METHODS);

        $nodeMock->method('getFullQualifiedName')->willReturn($fullyQualifiedClassName);
        $nodeMock->method('getName')->willReturn($nodeName);
        $nodeMock->method('getMethodNames')->willReturn(['fooMethod', 'barMethod']);

        return $nodeMock;
    }
}
