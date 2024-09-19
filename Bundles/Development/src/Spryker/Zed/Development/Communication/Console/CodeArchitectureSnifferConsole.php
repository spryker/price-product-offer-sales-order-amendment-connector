<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Development\Communication\Console;

use Generated\Shared\Transfer\ModuleFilterTransfer;
use Generated\Shared\Transfer\ModuleTransfer;
use Generated\Shared\Transfer\OrganizationTransfer;
use Spryker\Zed\Development\Business\ArchitectureSniffer\ArchitectureSniffer;
use Spryker\Zed\Development\Business\Normalizer\NameNormalizerInterface;
use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Spryker\Zed\Development\Business\DevelopmentFacadeInterface getFacade()
 * @method \Spryker\Zed\Development\Communication\DevelopmentCommunicationFactory getFactory()
 */
class CodeArchitectureSnifferConsole extends Console
{
    /**
     * @var string
     */
    protected const COMMAND_NAME = 'code:sniff:architecture';

    /**
     * @var string
     */
    protected const OPTION_MODULE = 'module';

    /**
     * @var string
     */
    protected const OPTION_STRICT = 'strict';

    /**
     * @var string
     */
    protected const OPTION_PRIORITY = 'priority';

    /**
     * @var string
     */
    protected const OPTION_DRY_RUN = 'dry-run';

    /**
     * @var string
     */
    protected const ARGUMENT_SUB_PATH = 'path';

    /**
     * @var string
     */
    protected const OPTION_VERBOSE = 'verbose';

    /**
     * @var string
     */
    protected const SOURCE_FOLDER_NAME = 'src';

    /**
     * @var string
     */
    protected const RUN_IN_STANDALONE_MODE = 'Run Architecture Sniffer in Standalone Mode';

    /**
     * @var \Spryker\Zed\Development\Business\Normalizer\NameNormalizerInterface|null $nameNormalizer
     */
    protected ?NameNormalizerInterface $nameNormalizer = null;

    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName(static::COMMAND_NAME)
            ->setHelp('<info>' . static::COMMAND_NAME . ' -h</info>')
            ->setDescription('Check architecture rules for project or core');

        $this->addOption(static::OPTION_MODULE, 'm', InputOption::VALUE_OPTIONAL, 'Name of module to run architecture sniffer for. You can use dot syntax for namespaced ones, e.g. `SprykerEco.FooBar`. `Spryker.all`/`SprykerShop.all` is reserved for CORE internal usage.');
        $this->addOption(static::OPTION_PRIORITY, 'p', InputOption::VALUE_OPTIONAL, 'Priority [1 (highest), 2 (medium), 3 (experimental)], defaults to 2.');
        $this->addOption(static::OPTION_STRICT, 's', InputOption::VALUE_NONE, 'Also report those nodes with a @SuppressWarnings annotation');
        $this->addOption(static::OPTION_DRY_RUN, 'd', InputOption::VALUE_NONE, 'Dry-Run the command, display it only');
        $this->addOption(ArchitectureSniffer::OPTION_UPDATE, 'u', InputOption::VALUE_NONE, 'Update baseline');

        $this->addArgument(static::ARGUMENT_SUB_PATH, InputArgument::OPTIONAL, 'Optional path or sub path element');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int Exit code
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var string $module */
        $module = $this->input->getOption(static::OPTION_MODULE);
        /** @var string|null $path */
        $path = $this->input->getArgument(static::ARGUMENT_SUB_PATH);
        $isCore = strpos((string)$module, '.') !== false;

        $message = $this->buildMessage($module, $path, $isCore);
        $this->info($message);

        if ($isCore) {
            $success = $this->runForCore($output, $module, $path);
        } else {
            $customPath = $this->getCustomPath($module, $path);

            if (!$module && file_exists($customPath)) {
                $success = $this->runCustomPath($output, $customPath);
            } else {
                $success = $this->runForProject($output, $module, $path);
            }
        }

        return $success ? static::CODE_SUCCESS : static::CODE_ERROR;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $moduleArgument
     * @param string $subPath
     *
     * @return bool
     */
    protected function runForCore(OutputInterface $output, $moduleArgument, $subPath): bool
    {
        $moduleTransferCollection = $this->getModulesToExecute($moduleArgument);

        if (!$moduleTransferCollection) {
            $customPath = $this->getCommonPath($moduleArgument, $subPath);

            if (file_exists($customPath)) {
                return $this->runCustomPath($output, $customPath);
            }
        }

        if (!$moduleTransferCollection) {
            $output->writeln(sprintf('<error>No module(s) found: `%s`.</error>', $moduleArgument));

            return false;
        }

        $count = 0;

        foreach ($moduleTransferCollection as $moduleTransfer) {
            $path = $this->getCorePath($moduleTransfer, $subPath);

            if (!is_dir($path)) {
                $output->writeln(sprintf('<error>Path not found: `%s`</error>', $path));

                return false;
            }

            $violations = $this->getFacade()->runArchitectureSniffer($path, $this->input->getOptions());
            $output->writeln($path, $violations ? OutputInterface::VERBOSITY_QUIET : OutputInterface::VERBOSITY_VERBOSE);
            $countCurrent = $this->displayViolationsWithBaseline($output, $violations);
            $count += $countCurrent;
        }

        return $count === 0;
    }

    /**
     * @param string $moduleArgument
     *
     * @return array<\Generated\Shared\Transfer\ModuleTransfer>
     */
    protected function getModulesToExecute(string $moduleArgument): array
    {
        return $this->getFacade()->getModules($this->buildModuleFilterTransfer($moduleArgument));
    }

    /**
     * @param string $moduleArgument
     *
     * @return \Generated\Shared\Transfer\ModuleFilterTransfer|null
     */
    protected function buildModuleFilterTransfer(string $moduleArgument): ?ModuleFilterTransfer
    {
        if (!$moduleArgument) {
            return null;
        }

        $moduleFilterTransfer = new ModuleFilterTransfer();

        if (strpos($moduleArgument, '.') === false) {
            $moduleTransfer = new ModuleTransfer();
            $moduleTransfer->setName($moduleArgument);
            $moduleFilterTransfer->setModule($moduleTransfer);

            return $moduleFilterTransfer;
        }

        $this->addModuleFilterDetails($moduleArgument, $moduleFilterTransfer);

        return $moduleFilterTransfer;
    }

    /**
     * @param string $moduleArgument
     * @param \Generated\Shared\Transfer\ModuleFilterTransfer $moduleFilterTransfer
     *
     * @return \Generated\Shared\Transfer\ModuleFilterTransfer
     */
    protected function addModuleFilterDetails(string $moduleArgument, ModuleFilterTransfer $moduleFilterTransfer): ModuleFilterTransfer
    {
        [$organization, $module] = explode('.', $moduleArgument);

        if ($module !== '*' && $module !== 'all') {
            $moduleTransfer = new ModuleTransfer();
            $moduleTransfer->setName($module);

            $moduleFilterTransfer->setModule($moduleTransfer);
        }

        $organizationTransfer = new OrganizationTransfer();
        $organizationTransfer->setName($organization);

        $moduleFilterTransfer->setOrganization($organizationTransfer);

        return $moduleFilterTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\ModuleTransfer $moduleTransfer
     * @param string $pathSuffix
     *
     * @return string
     */
    protected function getCorePath(ModuleTransfer $moduleTransfer, $pathSuffix): string
    {
        return $this->buildPath($moduleTransfer->getPath(), $pathSuffix);
    }

    /**
     * @param string $path
     * @param string|null $suffix
     *
     * @return string
     */
    protected function buildPath(string $path, ?string $suffix = null): string
    {
        return rtrim($path . $suffix, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $module
     * @param string $subPath
     *
     * @return bool
     */
    protected function runForProject(OutputInterface $output, $module, $subPath)
    {
        $pathToRoot = $this->getFactory()->getConfig()->getPathToRoot();
        $projectNamespaces = $this->getFactory()->getConfig()->getProjectNamespaces();

        $result = 0;
        $violations = null;
        foreach ($projectNamespaces as $projectNamespace) {
            $output->writeln($projectNamespace, OutputInterface::VERBOSITY_VERBOSE);
            $path = $pathToRoot . 'src' . DIRECTORY_SEPARATOR . $projectNamespace . DIRECTORY_SEPARATOR;

            $paths = [];
            if ($module) {
                foreach ($this->getFactory()->getConfig()->getApplicationLayers() as $layer) {
                    $paths[] = $path . $layer . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR;
                }
            } else {
                $paths = [$path];
            }

            foreach ($paths as $path) {
                if ($subPath) {
                    $path .= trim($subPath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                }
                if (!is_dir($path)) {
                    $output->writeln('Path not found, skipping: ' . $path, OutputInterface::VERBOSITY_VERY_VERBOSE);

                    continue;
                }
                $output->writeln('Checking path: ' . $path, OutputInterface::VERBOSITY_VERBOSE);

                $violations = $this->getFacade()->runArchitectureSniffer($path, $this->input->getOptions());
                $count = $this->displayViolationsWithBaseline($output, $violations);
                $result += $count;
            }
        }

        if ($violations === null) {
            $output->writeln('<error>No paths found for checking</error>');

            return false;
        }

        $this->displayViolationsCountMessage($output, $result);

        return $result === 0;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param string $customPath
     *
     * @return bool
     */
    protected function runCustomPath(OutputInterface $output, $customPath)
    {
        $output->writeln($customPath, OutputInterface::VERBOSITY_VERBOSE);

        $violations = $this->getFacade()->runArchitectureSniffer($customPath, $this->input->getOptions());
        $count = $this->displayViolationsWithBaseline($output, $violations);

        return $count === 0;
    }

    /**
     * @deprecated Use {@link \Spryker\Zed\Development\Communication\Console\CodeArchitectureSnifferConsole::displayViolationsWithBaseline()} instead.}
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param array $violations
     *
     * @return int
     */
    protected function displayViolations(OutputInterface $output, array $violations)
    {
        $count = 0;
        foreach ($violations as $violationArray) {
            foreach ($violationArray as $violation) {
                $output->writeln('<error> ' . trim($violation['_']) . ' (l. ' . $violation['beginline'] . ')</error>', OutputInterface::VERBOSITY_VERBOSE);
                $output->writeln(' ' . $violation['ruleset'] . ' > ' . $violation['rule'], OutputInterface::VERBOSITY_VERBOSE);
                $count++;
            }
        }

        return $count;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param array $violations
     *
     * @return int
     */
    protected function displayViolationsWithBaseline(OutputInterface $output, array $violations): int
    {
        if (!$violations) {
            return 0;
        }

        foreach ($violations as $type => $violationsArray) {
            $count = 0;

            foreach ($violationsArray as $violation) {
                if ($type === ArchitectureSniffer::NAME_VISIBLE_VIOLATIONS) {
                    $output->writeln('<error> - ' . trim($violation[ArchitectureSniffer::VIOLATION_FIELD_NAME_DESCRIPTION]) . '</error> ', OutputInterface::VERBOSITY_VERBOSE);
                } else {
                    $output->writeln(' - ' . trim($violation[ArchitectureSniffer::VIOLATION_FIELD_NAME_DESCRIPTION]), OutputInterface::VERBOSITY_VERBOSE);
                }

                $ruleset = $violation[ArchitectureSniffer::VIOLATION_FIELD_NAME_RULESET];
                $rule = $violation[ArchitectureSniffer::VIOLATION_FIELD_NAME_RULE];
                $filename = pathinfo($violation['fileName'], PATHINFO_FILENAME);
                $output->writeln('   ' . $ruleset . ' > ' . $rule . ' > ' . $filename, OutputInterface::VERBOSITY_VERBOSE);
                $count++;
            }

            $this->displayViolationsCountMessage($output, $count, ($type === ArchitectureSniffer::NAME_IGNORED_VIOLATIONS));
        }

        return count($violations[ArchitectureSniffer::NAME_VISIBLE_VIOLATIONS]);
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param int $count
     * @param bool $isIgnored
     *
     * @return void
     */
    protected function displayViolationsCountMessage(OutputInterface $output, int $count, $isIgnored = false): void
    {
        if (!$this->isVerboseModeEnabled() && $count === 0) {
            return;
        }

        $output->writeln($count . ($isIgnored ? ' ' . ArchitectureSniffer::NAME_IGNORED_VIOLATIONS : '') . ' violations found');
    }

    /**
     * @return bool
     */
    protected function isVerboseModeEnabled(): bool
    {
        return (bool)$this->input->getOption(static::OPTION_VERBOSE);
    }

    /**
     * @param string|null $module
     * @param string|null $path
     *
     * @return string
     */
    protected function getCustomPath(?string $module, ?string $path): string
    {
        $pathToRoot = $this->getFactory()->getConfig()->getPathToRoot();
        $customPath = $pathToRoot . $path;

        if (!$module && !$path) {
            return $this->buildPath($customPath, static::SOURCE_FOLDER_NAME);
        }

        return $this->buildPath($customPath);
    }

    /**
     * @param string|null $module
     * @param string|null $path
     *
     * @return string
     */
    protected function getCommonPath(?string $module, ?string $path): string
    {
        [$namespace, $module] = explode('.', $module);

        if (!$this->nameNormalizer) {
            $this->nameNormalizer = $this->getFactory()->createNameNormalizer();
        }

        $moduleVendor = $this->nameNormalizer->dasherize($namespace);
        $module = $this->nameNormalizer->dasherize($module);

        return sprintf(
            '%s/vendor/%s/%s/%s',
            $this->getFactory()->getConfig()->getPathToRoot(),
            $moduleVendor,
            $module,
            $path,
        );
    }

    /**
     * @param string|null $module
     * @param string|null $path
     * @param bool $isCore
     *
     * @return string
     */
    protected function buildMessage(?string $module = null, ?string $path = null, bool $isCore = true): string
    {
        if ($this->getFactory()->getConfig()->isStandaloneMode()) {
            return static::RUN_IN_STANDALONE_MODE;
        }

        $message = sprintf('Run Architecture Sniffer for %s', $isCore ? 'CORE' : 'PROJECT');

        if ($module !== null) {
            if (!$this->nameNormalizer) {
                $this->nameNormalizer = $this->getFactory()->createNameNormalizer();
            }

            $module = $this->nameNormalizer->camelize($module);

            $message = sprintf('%s in %s module', $message, $module);
        }

        if ($path) {
            $message = sprintf('%s (%s)', $message, $path);
        }

        return $message;
    }
}
