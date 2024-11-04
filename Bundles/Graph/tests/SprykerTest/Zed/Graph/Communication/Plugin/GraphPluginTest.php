<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Graph\Communication\Plugin;

use Codeception\Test\Unit;
use Spryker\Shared\Graph\GraphInterface;
use Spryker\Zed\Graph\Communication\Exception\GraphNotInitializedException;
use Spryker\Zed\Graph\Communication\GraphCommunicationFactory;
use Spryker\Zed\Graph\Communication\Plugin\GraphPlugin;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Graph
 * @group Communication
 * @group Plugin
 * @group GraphPluginTest
 * Add your own group annotations below this line
 */
class GraphPluginTest extends Unit
{
    /**
     * @var string
     */
    public const GRAPH_NAME = 'graph name';

    /**
     * @var string
     */
    public const NODE_A = 'node A';

    /**
     * @var string
     */
    public const NODE_B = 'node B';

    /**
     * @var string
     */
    public const GROUP_NAME = 'group name';

    /**
     * @var string
     */
    public const CLUSTER_NAME = 'cluster name';

    /**
     * @var array
     */
    public const ATTRIBUTES = ['attribute' => 'value', 'html attribute' => '<h1>Html Value</h1>'];

    /**
     * @return void
     */
    public function testGetGraphMustThrowExceptionIfGraphWasNotInitialized(): void
    {
        // Arrange
        $graphPlugin = new GraphPlugin();

        // Assert
        $this->expectException(GraphNotInitializedException::class);

        // Act
        $graphPlugin->addNode(static::NODE_A);
    }

    /**
     * @return void
     */
    public function testInit(): void
    {
        $graphMock = $this->getMockBuilder(GraphInterface::class)
            ->addMethods(['create'])
            ->onlyMethods(['addNode', 'addEdge', 'addCluster', 'render'])->getMock();
        $graphMock->method('render')->willReturn('');

        $factoryMock = $this->getMockBuilder(GraphCommunicationFactory::class)->getMock();
        $factoryMock->method('createGraph')->willReturn($graphMock);

        $pluginMock = $this->getMockBuilder(GraphPlugin::class)->onlyMethods(['getFactory'])->setConstructorArgs(['name'])->disableOriginalConstructor()->getMock();
        $pluginMock->method('getFactory')->willReturn($factoryMock);

        $this->assertInstanceOf(GraphPlugin::class, $this->getPluginMock()->init(static::GRAPH_NAME));
    }

    /**
     * @return void
     */
    public function testAddNode(): void
    {
        $this->assertInstanceOf(GraphPlugin::class, $this->getPluginMock()->addNode(static::NODE_A));
    }

    /**
     * @return void
     */
    public function testAddNodeWithAttributes(): void
    {
        $this->assertInstanceOf(GraphPlugin::class, $this->getPluginMock()->addNode(static::NODE_A, static::ATTRIBUTES));
    }

    /**
     * @return void
     */
    public function testAddNodeWithGroup(): void
    {
        $this->assertInstanceOf(GraphPlugin::class, $this->getPluginMock()->addNode(static::NODE_A, [], static::GROUP_NAME));
    }

    /**
     * @return void
     */
    public function testAddEdge(): void
    {
        $this->assertInstanceOf(GraphPlugin::class, $this->getPluginMock()->addEdge(static::NODE_A, static::NODE_B));
    }

    /**
     * @return void
     */
    public function testAddEdgeWithAttributes(): void
    {
        $this->assertInstanceOf(GraphPlugin::class, $this->getPluginMock()->addEdge(static::NODE_A, static::NODE_B, static::ATTRIBUTES));
    }

    /**
     * @return void
     */
    public function testAddCluster(): void
    {
        $this->assertInstanceOf(GraphPlugin::class, $this->getPluginMock()->addCluster(static::CLUSTER_NAME));
    }

    /**
     * @return void
     */
    public function testAddClusterWithAttributes(): void
    {
        $this->assertInstanceOf(GraphPlugin::class, $this->getPluginMock()->addCluster(static::CLUSTER_NAME, static::ATTRIBUTES));
    }

    /**
     * @return void
     */
    public function testRender(): void
    {
        $this->assertIsString($this->getPluginMock()->render('svg'));
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Graph\Communication\Plugin\GraphPlugin
     */
    protected function getPluginMock(): GraphPlugin
    {
        $graphMock = $this->getMockBuilder(GraphInterface::class)
            ->addMethods(['create'])
            ->onlyMethods(['addNode', 'addEdge', 'addCluster', 'render'])->getMock();
        $graphMock->method('render')->willReturn('');

        $factoryMock = $this->getMockBuilder(GraphCommunicationFactory::class)->getMock();
        $factoryMock->method('createGraph')->willReturn($graphMock);

        $pluginMock = $this->getMockBuilder(GraphPlugin::class)->onlyMethods(['getFactory'])->setConstructorArgs(['name'])->disableOriginalConstructor()->getMock();
        $pluginMock->method('getFactory')->willReturn($factoryMock);

        return $pluginMock->init(static::GRAPH_NAME);
    }
}
