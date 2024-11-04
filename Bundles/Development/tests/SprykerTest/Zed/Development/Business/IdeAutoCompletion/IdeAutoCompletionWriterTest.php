<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Development\Business\IdeAutoCompletion;

use Codeception\Test\Unit;
use Spryker\Zed\Development\Business\DevelopmentBusinessFactory;
use Spryker\Zed\Development\Business\IdeAutoCompletion\IdeAutoCompletionConstants;
use Spryker\Zed\Development\Business\IdeAutoCompletion\IdeAutoCompletionOptionConstants;
use Spryker\Zed\Development\DevelopmentConfig;
use Spryker\Zed\Development\DevelopmentDependencyProvider;
use Spryker\Zed\Kernel\Container;
use SprykerTest\Zed\Development\DevelopmentBusinessTester;
use SprykerTest\Zed\Development\Helper\IdeAutoCompletion;
use Symfony\Component\Finder\Finder;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Development
 * @group Business
 * @group IdeAutoCompletion
 * @group IdeAutoCompletionWriterTest
 * Add your own group annotations below this line
 */
class IdeAutoCompletionWriterTest extends Unit
{
    /**
     * @var \SprykerTest\Zed\Development\DevelopmentBusinessTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function testWriterCreatesYvesAutoCompletionFiles(): void
    {
        $tester = $this->createFunctionalTester();

        $tester->execute(function (): void {
            $this
                ->getDevelopmentBusinessFactory()
                ->createYvesIdeAutoCompletionWriter()
                ->writeCompletionFiles();
        });

        $tester->canSeeFileFound('AutoCompletion.php', IdeAutoCompletion::TEST_TARGET_DIRECTORY . 'Generated/Yves/Ide/');
        $finder = new Finder();
        $finder->in(IdeAutoCompletion::TEST_TARGET_DIRECTORY . 'Generated/Yves/Ide/');

        $this->assertTrue($finder->count() > 1);
    }

    /**
     * @return void
     */
    public function testWriterCreatesZedAutoCompletionFiles(): void
    {
        $tester = $this->createFunctionalTester();

        $tester->execute(function (): void {
            $this
                ->getDevelopmentBusinessFactory()
                ->createZedIdeAutoCompletionWriter()
                ->writeCompletionFiles();
        });

        $tester->canSeeFileFound('AutoCompletion.php', IdeAutoCompletion::TEST_TARGET_DIRECTORY . 'Generated/Zed/Ide/');

        $finder = new Finder();
        $finder->in(IdeAutoCompletion::TEST_TARGET_DIRECTORY . 'Generated/Zed/Ide/');

        $this->assertTrue($finder->count() > 1);
    }

    /**
     * @return void
     */
    public function testWriterCreatesClientAutoCompletionFiles(): void
    {
        $tester = $this->createFunctionalTester();

        $tester->execute(function (): void {
            $this
                ->getDevelopmentBusinessFactory()
                ->createClientIdeAutoCompletionWriter()
                ->writeCompletionFiles();
        });

        $tester->canSeeFileFound('AutoCompletion.php', IdeAutoCompletion::TEST_TARGET_DIRECTORY . 'Generated/Client/Ide/');

        $finder = new Finder();
        $finder->in(IdeAutoCompletion::TEST_TARGET_DIRECTORY . 'Generated/Client/Ide/');

        $this->assertTrue($finder->count() > 1);
    }

    /**
     * @return void
     */
    public function testWriterCreatesServiceAutoCompletionFiles(): void
    {
        $tester = $this->createFunctionalTester();

        $tester->execute(function (): void {
            $this
                ->getDevelopmentBusinessFactory()
                ->createServiceIdeAutoCompletionWriter()
                ->writeCompletionFiles();
        });

        $tester->canSeeFileFound('AutoCompletion.php', IdeAutoCompletion::TEST_TARGET_DIRECTORY . 'Generated/Service/Ide/');
    }

    /**
     * @return void
     */
    public function testWriterCreatesGlueAutoCompletionFiles(): void
    {
        $tester = $this->createFunctionalTester();

        $tester->execute(function (): void {
            $this
                ->getDevelopmentBusinessFactory()
                ->createGlueIdeAutoCompletionWriter()
                ->writeCompletionFiles();
        });

        $tester->canSeeFileFound('AutoCompletion.php', IdeAutoCompletion::TEST_TARGET_DIRECTORY . 'Generated/Glue/Ide/');
    }

    /**
     * @return void
     */
    public function testWriterCreatesGlueBackendAutoCompletionFiles(): void
    {
        $tester = $this->createFunctionalTester();

        $tester->execute(function (): void {
            $this
                ->getDevelopmentBusinessFactory()
                ->createGlueBackendIdeAutoCompletionWriter()
                ->writeCompletionFiles();
        });

        $tester->canSeeFileFound('AutoCompletion.php', IdeAutoCompletion::TEST_TARGET_DIRECTORY . 'Generated/GlueBackend/Ide/');
    }

    /**
     * @return \SprykerTest\Zed\Development\DevelopmentBusinessTester
     */
    protected function createFunctionalTester(): DevelopmentBusinessTester
    {
        return $this->tester;
    }

    /**
     * @return \Spryker\Zed\Development\Business\DevelopmentBusinessFactory
     */
    protected function getDevelopmentBusinessFactory(): DevelopmentBusinessFactory
    {
        $container = new Container();
        $dependencyProvider = new DevelopmentDependencyProvider();
        $container = $dependencyProvider->provideBusinessLayerDependencies($container);

        $factory = new DevelopmentBusinessFactory();
        $factory->setContainer($container);

        $factory->setConfig($this->getDevelopmentConfigMock());

        return $factory;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Development\DevelopmentConfig
     */
    protected function getDevelopmentConfigMock(): DevelopmentConfig
    {
        $configMock = $this
            ->getMockBuilder(DevelopmentConfig::class)
            ->onlyMethods(['getDefaultIdeAutoCompletionOptions'])
            ->getMock();

        $configMock
            ->method('getDefaultIdeAutoCompletionOptions')
            ->willReturn([
                IdeAutoCompletionOptionConstants::TARGET_BASE_DIRECTORY => IdeAutoCompletion::TEST_TARGET_DIRECTORY,
                IdeAutoCompletionOptionConstants::TARGET_DIRECTORY_PATTERN => sprintf(
                    'Generated/%s/Ide',
                    IdeAutoCompletionConstants::APPLICATION_NAME_PLACEHOLDER,
                ),
                IdeAutoCompletionOptionConstants::TARGET_NAMESPACE_PATTERN => sprintf(
                    'Generated\%s\Ide',
                    IdeAutoCompletionConstants::APPLICATION_NAME_PLACEHOLDER,
                ),
                IdeAutoCompletionConstants::DIRECTORY_PERMISSION => 0777,
            ]);

        return $configMock;
    }
}
