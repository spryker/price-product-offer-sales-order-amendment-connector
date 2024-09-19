<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Development\Business\CodeBuilder\Module;

use Laminas\Filter\FilterChain;
use Laminas\Filter\Word\CamelCaseToDash;
use Spryker\Zed\Development\DevelopmentConfig;
use Symfony\Component\Filesystem\Filesystem;

class ModuleBuilder
{
    /**
     * @var string
     */
    protected const OPTION_FILE = 'file';

    /**
     * @var string
     */
    protected const OPTION_FORCE = 'force';

    /**
     * @var string
     */
    protected const NAMESPACE_SPRYKER = 'Spryker';

    /**
     * @var \Spryker\Zed\Development\DevelopmentConfig
     */
    protected $config;

    /**
     * List of files to generate in each module.
     *
     * The codecept alias is required to not trigger codeception autoloading.
     *
     * @var array
     */
    protected $files = [
        '.gitattributes',
        '.gitignore',
        'CHANGELOG.md',
        'codecept.yml' => 'codeception.yml',
        'composer.json',
        'LICENSE',
        'phpstan.neon',
        'README.md',
        'tooling.yml',
    ];

    /**
     * @param \Spryker\Zed\Development\DevelopmentConfig $config
     */
    public function __construct(DevelopmentConfig $config)
    {
        $this->config = $config;
    }

    /**
     * @param string $module
     * @param array<string, mixed> $options
     *
     * @return void
     */
    public function build($module, array $options)
    {
        $namespace = static::NAMESPACE_SPRYKER;
        if (strpos($module, '.') !== false) {
            [$namespace, $module] = explode('.', $module, 2);
        }

        if ($module !== 'all') {
            $module = $this->getUnderscoreToCamelCaseFilter()->filter($module);
            $modules = (array)$module;
        } else {
            $modules = $this->getModuleNames($namespace);
        }

        foreach ($modules as $module) {
            $this->createOrUpdateModule($namespace, $module, $options);
        }
    }

    /**
     * @return \Laminas\Filter\FilterChain
     */
    protected function getUnderscoreToCamelCaseFilter()
    {
        $filter = new FilterChain();

        $filter->attachByName('WordUnderscoreToCamelCase');

        return $filter;
    }

    /**
     * @param string $namespace
     *
     * @return array<string>
     */
    protected function getModuleNames($namespace)
    {
        $moduleDirectory = $this->getDirectoryName($namespace);

        /** @var iterable $moduleDirectories */
        $moduleDirectories = glob($moduleDirectory . '*', GLOB_NOSORT);

        $modules = [];
        foreach ($moduleDirectories as $moduleDirectory) {
            $modules[] = pathinfo($moduleDirectory, PATHINFO_BASENAME);
        }

        return $modules;
    }

    /**
     * @param string $namespace
     * @param string $module
     * @param array<string, mixed> $options
     *
     * @return void
     */
    protected function createOrUpdateModule($namespace, $module, array $options)
    {
        foreach ($this->files as $alias => $file) {
            $source = $file;
            if (is_string($alias)) {
                $source = $alias;
            }

            if (!empty($options[static::OPTION_FILE]) && $file !== $options[static::OPTION_FILE]) {
                continue;
            }

            $templateContent = $this->getTemplateContent($source);

            $templateContent = $this->replacePlaceHolder($namespace, $module, $templateContent);

            $this->saveFile($namespace, $module, $templateContent, $file, $options[static::OPTION_FORCE]);
        }
    }

    /**
     * @param string $templateName
     *
     * @return string
     */
    protected function getTemplateContent($templateName)
    {
        /** @phpstan-var string */
        return file_get_contents(
            __DIR__ . DIRECTORY_SEPARATOR . 'Templates' . DIRECTORY_SEPARATOR . $templateName . '.tpl',
        );
    }

    /**
     * @param string $namespace
     * @param string $module
     * @param string $templateContent
     *
     * @return string
     */
    protected function replacePlaceHolder($namespace, $module, $templateContent)
    {
        $from = [
            '{module}',
            '{moduleVariable}',
            '{moduleDashed}',
            '{namespace}',
            '{namespaceDashed}',
        ];

        $to = [
            $module,
            lcfirst($module),
            $this->camelCaseToDash($module),
            $namespace,
            $this->camelCaseToDash($namespace),
        ];

        $templateContent = str_replace(
            $from,
            $to,
            $templateContent,
        );

        return $templateContent;
    }

    /**
     * @param string $module
     *
     * @return string
     */
    protected function camelCaseToDash($module)
    {
        $filter = new CamelCaseToDash();

        /** @var string $camelCasedModule */
        $camelCasedModule = $filter->filter($module);
        $module = strtolower($camelCasedModule);

        return $module;
    }

    /**
     * @param string $namespace
     * @param string $module
     * @param string $templateContent
     * @param string $fileName
     * @param bool $overwrite
     *
     * @return void
     */
    protected function saveFile($namespace, $module, $templateContent, $fileName, $overwrite = false)
    {
        $path = $this->getDirectoryName($namespace) . $this->getModuleName($module, $namespace) . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            mkdir($path, $this->config->getPermissionMode(), true);
        }

        $filesystem = new Filesystem();
        $filePath = $path . $fileName;
        if (!is_file($filePath) || $overwrite) {
            $filesystem->dumpFile($filePath, $templateContent);
        }
    }

    /**
     * @param string $namespace
     *
     * @return string
     */
    protected function getDirectoryName($namespace)
    {
        $pathToInternalNamespace = $this->config->getPathToInternalNamespace($namespace);
        if ($pathToInternalNamespace) {
            return $pathToInternalNamespace;
        }

        $folder = $this->camelCaseToDash($namespace);

        return $this->config->getPathToRoot() . 'vendor' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR;
    }

    /**
     * @param string $module
     * @param string $namespace
     *
     * @return string
     */
    protected function getModuleName($module, $namespace)
    {
        if (in_array($namespace, $this->config->getInternalNamespacesList(), true)) {
            return $module;
        }

        return $this->camelCaseToDash($module);
    }
}
