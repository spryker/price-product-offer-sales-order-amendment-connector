<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Development\Business\ArchitectureSniffer;

use InvalidArgumentException;
use Laminas\Filter\FilterInterface;
use Spryker\Zed\Development\DevelopmentConfig;
use Symfony\Component\Finder\Finder;

class AllModuleFinder implements AllModuleFinderInterface
{
    /**
     * @var \Symfony\Component\Finder\Finder
     */
    protected $finder;

    /**
     * @var \Spryker\Zed\Development\DevelopmentConfig
     */
    protected $developmentConfig;

    /**
     * @var \Laminas\Filter\FilterInterface
     */
    protected $filter;

    /**
     * @param \Symfony\Component\Finder\Finder $finder
     * @param \Spryker\Zed\Development\DevelopmentConfig $developmentConfig
     * @param \Laminas\Filter\FilterInterface $filter
     */
    public function __construct(Finder $finder, DevelopmentConfig $developmentConfig, FilterInterface $filter)
    {
        $this->finder = $finder;
        $this->developmentConfig = $developmentConfig;
        $this->filter = $filter;
    }

    /**
     * @return array
     */
    public function find(): array
    {
        $modules = [];
        $modules[] = $this->loadProjectModules();
        $modules[] = $this->loadCoreDevelopmentModules();
        $modules[] = $this->loadFeaturesDevelopmentModules();
        $modules[] = $this->loadOtherCoreModules();

        return $this->addApplication(array_merge(...$modules));
    }

    /**
     * @return array
     */
    protected function loadProjectModules(): array
    {
        $modules = [];
        foreach ($this->developmentConfig->getProjectNamespaces() as $projectNamespace) {
            $path = APPLICATION_SOURCE_DIR . '/' . $projectNamespace . '/*';
            $modules = $this->findModules($path, $projectNamespace);
        }

        return $modules;
    }

    /**
     * @return array
     */
    protected function loadCoreDevelopmentModules(): array
    {
        $modules = [];
        foreach ($this->developmentConfig->getInternalNamespacesList() as $internalNamespace) {
            foreach (range('A', 'Z') as $letter) {
                $namespaceDir = $this->filter->filter($internalNamespace);
                $namespaceDir = strtolower($namespaceDir);

                $path = sprintf('%s/spryker/%s/Bundles/%s*/src/*/*', APPLICATION_VENDOR_DIR, $namespaceDir, $letter);
                $modules[] = $this->findModules($path, $internalNamespace);
            }
        }

        return array_merge(...$modules);
    }

    /**
     * @return array
     */
    protected function loadFeaturesDevelopmentModules(): array
    {
        $modules = [];

        foreach (range('A', 'Z') as $letter) {
            $path = sprintf('%s/spryker/%s/Features/%s*/src/*/*', APPLICATION_VENDOR_DIR, 'spryker', $letter);
            $modules[] = $this->findModules($path, $this->developmentConfig->getSprykerFeatureNamespace());
        }

        return array_merge(...$modules);
    }

    /**
     * @return array
     */
    protected function loadOtherCoreModules(): array
    {
        $modules = [];
        foreach ($this->developmentConfig->getCoreNamespaces() as $coreNamespace) {
            foreach (range('a', 'z') as $letter) {
                $namespaceDir = $this->filter->filter($coreNamespace);
                $namespaceDir = strtolower($namespaceDir);

                $path = sprintf('%s/%s/%s*/src/*/*', APPLICATION_VENDOR_DIR, $namespaceDir, $letter);
                $modules[] = $this->findModules($path, $coreNamespace);
            }
        }

        return array_merge(...$modules);
    }

    /**
     * @param string $path
     * @param string $namespace
     *
     * @return array
     */
    protected function findModules($path, $namespace): array
    {
        $finder = clone $this->finder;

        try {
            /** @var array<\SplFileInfo> $directories */
            $directories = $finder
                ->directories()
                ->in($path)
                ->depth('== 0');
        } catch (InvalidArgumentException $e) {
            // ~ Directory does not exist. It's not an error.
            return [];
        }

        if (!$directories) {
            return [];
        }

        $modules = [];
        foreach ($directories as $dir) {
            $modules[] = [
                'bundle' => $dir->getFileName(),
                'namespace' => $namespace,
                'directory' => $dir->getPathName(),
            ];
        }

        return $modules;
    }

    /**
     * @param array<array<string, mixed>> $modules
     *
     * @return array<array<string, mixed>>
     */
    protected function addApplication(array $modules): array
    {
        foreach ($modules as $i => $moduleData) {
            $moduleDataExploded = explode('/', $moduleData['directory']);
            $modules[$i]['application'] = $moduleDataExploded[count($moduleDataExploded) - 2];
        }

        return $modules;
    }
}
