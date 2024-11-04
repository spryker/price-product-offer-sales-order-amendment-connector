<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Cache\Business\Model;

use Codeception\Test\Unit;
use Spryker\Zed\Cache\Business\Model\CacheClearer;
use Spryker\Zed\Cache\CacheConfig;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Cache
 * @group Business
 * @group Model
 * @group CacheClearerTest
 * Add your own group annotations below this line
 */
class CacheClearerTest extends Unit
{
    /**
     * @return void
     */
    public function testClearCacheEmptiesDirectories(): void
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cache\CacheConfig $configMock
         */
        $configMock = $this->getConfigMock(['DE']);
        $configMock
            ->expects($this->once())
            ->method('getCachePath')
            ->will($this->returnValue('/path/to/cache'));

        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Filesystem\Filesystem $fileSystemMock
         */
        $fileSystemMock = $this->getFileSystemMock();
        $fileSystemMock
            ->expects($this->once())
            ->method('exists')
            ->with($this->equalTo('/path/to/cache'))
            ->will($this->returnValue(true));
        $fileSystemMock
            ->expects($this->once())
            ->method('remove');

        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Finder\Finder $finderMock
         */
        $finderMock = $this->getFinderMock();
        $finderMock
            ->expects($this->once())
            ->method('in')
            ->with($this->equalTo('/path/to/cache'))
            ->will($this->returnSelf());

        $finderMock
            ->expects($this->once())
            ->method('depth')
            ->will($this->returnSelf());

        $cacheClearer = new CacheClearer($configMock, $fileSystemMock, $finderMock);
        $cacheClearer->clearCache();
    }

    /**
     * @return void
     */
    public function testClearAutoLoadCacheEmptiesDirectories(): void
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cache\CacheConfig $configMock
         */
        $configMock = $this->getConfigMock(['DE']);
        $configMock
            ->expects($this->once())
            ->method('getAutoloaderCachePath')
            ->will($this->returnValue('/path/to/auto-load-cache'));

        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Filesystem\Filesystem $fileSystemMock
         */
        $fileSystemMock = $this->getFileSystemMock();
        $fileSystemMock
            ->expects($this->once())
            ->method('exists')
            ->with($this->equalTo('/path/to/auto-load-cache'))
            ->will($this->returnValue(true));
        $fileSystemMock
            ->expects($this->once())
            ->method('remove');

        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Finder\Finder $finderMock
         */
        $finderMock = $this->getFinderMock();
        $finderMock
            ->expects($this->once())
            ->method('in')
            ->with($this->equalTo('/path/to/auto-load-cache'))
            ->will($this->returnSelf());

        $finderMock
            ->expects($this->once())
            ->method('depth')
            ->will($this->returnSelf());

        $cacheClearer = new CacheClearer($configMock, $fileSystemMock, $finderMock);
        $cacheClearer->clearAutoLoaderCache();
    }

    /**
     * @return void
     */
    public function testClearingOfFilesForAllStores(): void
    {
        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cache\CacheConfig $configMock
         */
        $configMock = $this->getConfigMock(['DE', 'EN']);
        $configMock
            ->expects($this->once())
            ->method('getCachePath')
            ->will($this->returnValue('/path/to/{STORE}/cache'));

        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Filesystem\Filesystem $fileSystemMock
         */
        $fileSystemMock = $this->getFileSystemMock();
        $matcher = $this->exactly(2);
        $fileSystemMock
            ->expects($matcher)
            ->method('exists')
            ->willReturnCallback(function ($path) use ($matcher) {
                match ($matcher->numberOfInvocations()) {
                    1 => $this->assertEquals('/path/to/DE/cache', $path),
                    2 => $this->assertEquals('/path/to/EN/cache', $path),
                };

                return true;
            });

        $fileSystemMock
            ->expects($this->exactly(2))
            ->method('remove');

        /**
         * @var \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Finder\Finder $finderMock
         */
        $finderMock = $this->getFinderMock();
        $matcherFinder = $this->exactly(2);
        $finderMock
            ->expects($matcherFinder)
            ->method('in')
            ->willReturnCallback(function ($path) use ($matcherFinder, $finderMock) {
                match ($matcherFinder->numberOfInvocations()) {
                    1 => $this->assertEquals('/path/to/DE/cache', $path),
                    2 => $this->assertEquals('/path/to/EN/cache', $path),
                };

                return $finderMock;
            });

        $finderMock
            ->expects($this->exactly(2))
            ->method('depth')
            ->will($this->returnSelf());

        $cacheClearer = new CacheClearer($configMock, $fileSystemMock, $finderMock);
        $cacheClearer->clearCache();
    }

    /**
     * @param array<string> $stores
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cache\CacheConfig
     */
    protected function getConfigMock(array $stores): CacheConfig
    {
        $mock = $this
            ->getMockBuilder(CacheConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mock
            ->expects($this->any())
            ->method('getAllowedStores')
            ->will($this->returnValue($stores));

        $mock
            ->expects($this->any())
            ->method('getStorePatternMarker')
            ->will($this->returnValue(CacheConfig::STORE_PATTERN_MARKER));

        return $mock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Filesystem\Filesystem
     */
    protected function getFileSystemMock(): Filesystem
    {
        return $this
            ->getMockBuilder(Filesystem::class)
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Finder\Finder
     */
    protected function getFinderMock(): Finder
    {
        return $this
            ->getMockBuilder(Finder::class)
            ->getMock();
    }
}
