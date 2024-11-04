<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Development\Business\IdeAutoCompletion\Bundle\MethodBuilder;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\IdeAutoCompletionBundleTransfer;
use Spryker\Zed\Development\Business\IdeAutoCompletion\Bundle\MethodBuilder\QueryContainerMethodBuilder;
use Spryker\Zed\Development\Business\IdeAutoCompletion\Bundle\NamespaceExtractor;
use Spryker\Zed\Development\Business\IdeAutoCompletion\Bundle\NamespaceExtractorInterface;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Development
 * @group Business
 * @group IdeAutoCompletion
 * @group Bundle
 * @group MethodBuilder
 * @group QueryContainerMethodBuilderTest
 * Add your own group annotations below this line
 */
class QueryContainerMethodBuilderTest extends Unit
{
    /**
     * @var string
     */
    public const BASE_DIRECTORY = '/foo/bar/baz/*/src/';

    /**
     * @var string
     */
    public const BUNDLE_DIRECTORY = '/foo/bar/baz/FooBundle/src/Spryker/FooApplication/';

    /**
     * @return void
     */
    public function testMethodNameIsQueryConntainer(): void
    {
        $methodBuilderMock = $this->getQueryContainerMethodBuilderMock();
        $methodBuilderMock
            ->expects($this->any())
            ->method('findFileByName')
            ->willReturn(new SplFileInfo(static::BUNDLE_DIRECTORY . 'FooBundle/Persistence/FooBundleQueryContainerInterface.php', 'foo', 'bar'));

        $bundleMethodTransfer = $methodBuilderMock->getMethod($this->getBundleTransfer());

        $this->assertSame('queryContainer', $bundleMethodTransfer->getName());
    }

    /**
     * @return void
     */
    public function testFileLookupIsPerformedInPersistenceLayer(): void
    {
        $methodBuilderMock = $this->getQueryContainerMethodBuilderMock();
        $methodBuilderMock
            ->expects($this->any())
            ->method('findFileByName')
            ->with($this->anything(), $this->equalTo(static::BUNDLE_DIRECTORY . 'FooBundle/Persistence/'));

        $methodBuilderMock->getMethod($this->getBundleTransfer());
    }

    /**
     * @return \Spryker\Zed\Development\Business\IdeAutoCompletion\Bundle\MethodBuilder\QueryContainerMethodBuilder|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getQueryContainerMethodBuilderMock(): QueryContainerMethodBuilder
    {
        $methodBuilderMock = $this
            ->getMockBuilder(QueryContainerMethodBuilder::class)
            ->setConstructorArgs([$this->getNamespaceExtractorMock()])
            ->onlyMethods(['findFileByName', 'isSearchDirectoryAccessible'])
            ->getMock();

        $methodBuilderMock
            ->expects($this->any())
            ->method('isSearchDirectoryAccessible')
            ->willReturn(true);

        return $methodBuilderMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Development\Business\IdeAutoCompletion\Bundle\NamespaceExtractorInterface
     */
    protected function getNamespaceExtractorMock(): NamespaceExtractorInterface
    {
        return $this
            ->getMockBuilder(NamespaceExtractor::class)
            ->getMock();
    }

    /**
     * @return \Generated\Shared\Transfer\IdeAutoCompletionBundleTransfer
     */
    protected function getBundleTransfer(): IdeAutoCompletionBundleTransfer
    {
        $bundleTransfer = new IdeAutoCompletionBundleTransfer();
        $bundleTransfer->setName('FooBundle');
        $bundleTransfer->setNamespaceName('Generated\FooApplication\Ide');
        $bundleTransfer->setBaseDirectory(static::BASE_DIRECTORY);
        $bundleTransfer->setDirectory(static::BUNDLE_DIRECTORY);
        $bundleTransfer->setMethodName('fooBundle');

        return $bundleTransfer;
    }
}
