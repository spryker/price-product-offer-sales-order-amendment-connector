<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AclMerchantPortal\Business;

use Spryker\Zed\AclMerchantPortal\AclMerchantPortalDependencyProvider;
use Spryker\Zed\AclMerchantPortal\Business\Adder\GroupAdder;
use Spryker\Zed\AclMerchantPortal\Business\Adder\GroupAdderInterface;
use Spryker\Zed\AclMerchantPortal\Business\Checker\AclRoleAssignmentChecker;
use Spryker\Zed\AclMerchantPortal\Business\Checker\AclRoleAssignmentCheckerInterface;
use Spryker\Zed\AclMerchantPortal\Business\Checker\MerchantUserRestrictionChecker;
use Spryker\Zed\AclMerchantPortal\Business\Checker\MerchantUserRestrictionCheckerInterface;
use Spryker\Zed\AclMerchantPortal\Business\ConditionChecker\MerchantUser\UserRoleFilterConditionChecker;
use Spryker\Zed\AclMerchantPortal\Business\ConditionChecker\MerchantUser\UserRoleFilterConditionCheckerInterface;
use Spryker\Zed\AclMerchantPortal\Business\Creator\AclEntityCreator;
use Spryker\Zed\AclMerchantPortal\Business\Creator\AclEntityCreatorInterface;
use Spryker\Zed\AclMerchantPortal\Business\Creator\AclEntityRuleCreator;
use Spryker\Zed\AclMerchantPortal\Business\Creator\AclEntityRuleCreatorInterface;
use Spryker\Zed\AclMerchantPortal\Business\Creator\AclEntitySegmentCreator;
use Spryker\Zed\AclMerchantPortal\Business\Creator\AclEntitySegmentCreatorInterface;
use Spryker\Zed\AclMerchantPortal\Business\Creator\AclRoleCreator;
use Spryker\Zed\AclMerchantPortal\Business\Creator\AclRoleCreatorInterface;
use Spryker\Zed\AclMerchantPortal\Business\Creator\AclRuleCreator;
use Spryker\Zed\AclMerchantPortal\Business\Creator\AclRuleCreatorInterface;
use Spryker\Zed\AclMerchantPortal\Business\Expander\AclEntity\AclEntityMetadataConfigExpander;
use Spryker\Zed\AclMerchantPortal\Business\Expander\AclEntity\AclEntityMetadataConfigExpanderInterface;
use Spryker\Zed\AclMerchantPortal\Business\Expander\AclEntityConfigurationExpander;
use Spryker\Zed\AclMerchantPortal\Business\Expander\AclEntityConfigurationExpanderInterface;
use Spryker\Zed\AclMerchantPortal\Business\Expander\AgentDashboardMerchantUserTableExpander;
use Spryker\Zed\AclMerchantPortal\Business\Expander\AgentDashboardMerchantUserTableExpanderInterface;
use Spryker\Zed\AclMerchantPortal\Business\Filter\AclEntityRuleFilter;
use Spryker\Zed\AclMerchantPortal\Business\Filter\AclEntityRuleFilterInterface;
use Spryker\Zed\AclMerchantPortal\Business\Generator\AclMerchantPortalGenerator;
use Spryker\Zed\AclMerchantPortal\Business\Generator\AclMerchantPortalGeneratorInterface;
use Spryker\Zed\AclMerchantPortal\Business\Reader\MerchantReader;
use Spryker\Zed\AclMerchantPortal\Business\Reader\MerchantReaderInterface;
use Spryker\Zed\AclMerchantPortal\Business\Reader\MerchantUserReader;
use Spryker\Zed\AclMerchantPortal\Business\Reader\MerchantUserReaderInterface;
use Spryker\Zed\AclMerchantPortal\Business\Saver\AclEntitySaver;
use Spryker\Zed\AclMerchantPortal\Business\Saver\AclEntitySaverInterface;
use Spryker\Zed\AclMerchantPortal\Business\Saver\AclRoleSaver;
use Spryker\Zed\AclMerchantPortal\Business\Saver\AclRoleSaverInterface;
use Spryker\Zed\AclMerchantPortal\Business\Synchronizer\AclEntitySynchronizer;
use Spryker\Zed\AclMerchantPortal\Business\Synchronizer\AclEntitySynchronizerInterface;
use Spryker\Zed\AclMerchantPortal\Business\Writer\AclMerchantPortalWriter;
use Spryker\Zed\AclMerchantPortal\Business\Writer\AclMerchantPortalWriterInterface;
use Spryker\Zed\AclMerchantPortal\Dependency\Facade\AclMerchantPortalToAclEntityFacadeInterface;
use Spryker\Zed\AclMerchantPortal\Dependency\Facade\AclMerchantPortalToAclFacadeInterface;
use Spryker\Zed\AclMerchantPortal\Dependency\Facade\AclMerchantPortalToMerchantFacadeInterface;
use Spryker\Zed\AclMerchantPortal\Dependency\Facade\AclMerchantPortalToMerchantUserFacadeInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \Spryker\Zed\AclMerchantPortal\AclMerchantPortalConfig getConfig()
 */
class AclMerchantPortalBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @deprecated Use {@link \Spryker\Zed\AclMerchantPortal\Business\AclMerchantPortalBusinessFactory::createAclEntityCreator()} instead.
     *
     * @return \Spryker\Zed\AclMerchantPortal\Business\Writer\AclMerchantPortalWriterInterface
     */
    public function createAclMerchantPortalWriter(): AclMerchantPortalWriterInterface
    {
        return new AclMerchantPortalWriter(
            $this->getAclFacade(),
            $this->getAclEntityFacade(),
            $this->createAclMerchantPortalGenerator(),
            $this->getConfig(),
        );
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Generator\AclMerchantPortalGeneratorInterface
     */
    public function createAclMerchantPortalGenerator(): AclMerchantPortalGeneratorInterface
    {
        return new AclMerchantPortalGenerator(
            $this->getConfig(),
        );
    }

    /**
     * @deprecated Use {@link \Spryker\Zed\AclMerchantPortal\Business\AclMerchantPortalBusinessFactory::createAclEntityConfigurationExpander()} instead.
     *
     * @return \Spryker\Zed\AclMerchantPortal\Business\Expander\AclEntity\AclEntityMetadataConfigExpanderInterface
     */
    public function createAclEntityMetadataConfigExpander(): AclEntityMetadataConfigExpanderInterface
    {
        return new AclEntityMetadataConfigExpander();
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\ConditionChecker\MerchantUser\UserRoleFilterConditionCheckerInterface
     */
    public function createUserRoleFilterConditionChecker(): UserRoleFilterConditionCheckerInterface
    {
        return new UserRoleFilterConditionChecker(
            $this->getConfig(),
            $this->getAclFacade(),
        );
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Checker\MerchantUserRestrictionCheckerInterface
     */
    public function createMerchantUserRestrictionChecker(): MerchantUserRestrictionCheckerInterface
    {
        return new MerchantUserRestrictionChecker($this->getAclFacade());
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Creator\AclEntityCreatorInterface
     */
    public function createAclEntityCreator(): AclEntityCreatorInterface
    {
        return new AclEntityCreator(
            $this->getAclFacade(),
            $this->createAclMerchantPortalGenerator(),
            $this->createAclRoleCreator(),
            $this->createGroupAdder(),
        );
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Creator\AclRoleCreatorInterface
     */
    public function createAclRoleCreator(): AclRoleCreatorInterface
    {
        return new AclRoleCreator(
            $this->getAclFacade(),
            $this->createAclEntitySegmentCreator(),
            $this->createAclRuleCreator(),
            $this->createAclEntityRuleCreator(),
            $this->createAclMerchantPortalGenerator(),
        );
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Saver\AclRoleSaverInterface
     */
    public function createAclRoleSaver(): AclRoleSaverInterface
    {
        return new AclRoleSaver(
            $this->getAclFacade(),
            $this->createAclEntitySegmentCreator(),
            $this->createAclRuleCreator(),
            $this->createAclEntityRuleCreator(),
            $this->createAclMerchantPortalGenerator(),
            $this->getAclEntityFacade(),
        );
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Creator\AclEntitySegmentCreatorInterface
     */
    public function createAclEntitySegmentCreator(): AclEntitySegmentCreatorInterface
    {
        return new AclEntitySegmentCreator(
            $this->getAclEntityFacade(),
            $this->createAclMerchantPortalGenerator(),
        );
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Creator\AclRuleCreatorInterface
     */
    public function createAclRuleCreator(): AclRuleCreatorInterface
    {
        return new AclRuleCreator(
            $this->getAclFacade(),
            $this->getMerchantAclRuleExpanderPlugins(),
            $this->getMerchantUserAclRuleExpanderPlugins(),
        );
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Creator\AclEntityRuleCreatorInterface
     */
    public function createAclEntityRuleCreator(): AclEntityRuleCreatorInterface
    {
        return new AclEntityRuleCreator(
            $this->getAclEntityFacade(),
            $this->createAclEntityRuleFilter(),
            $this->getMerchantAclEntityRuleExpanderPlugins(),
            $this->getMerchantUserAclEntityRuleExpanderPlugins(),
        );
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Adder\GroupAdderInterface
     */
    public function createGroupAdder(): GroupAdderInterface
    {
        return new GroupAdder(
            $this->getAclFacade(),
            $this->createAclMerchantPortalGenerator(),
            $this->getConfig(),
        );
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Expander\AclEntityConfigurationExpanderInterface
     */
    public function createAclEntityConfigurationExpander(): AclEntityConfigurationExpanderInterface
    {
        return new AclEntityConfigurationExpander(
            $this->getAclEntityConfigurationExpanderPlugins(),
        );
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Expander\AgentDashboardMerchantUserTableExpanderInterface
     */
    public function createAgentDashboardMerchantUserTableExpander(): AgentDashboardMerchantUserTableExpanderInterface
    {
        return new AgentDashboardMerchantUserTableExpander($this->getConfig(), $this->getAclFacade());
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Reader\MerchantReaderInterface
     */
    public function createMerchantReader(): MerchantReaderInterface
    {
        return new MerchantReader(
            $this->getConfig(),
            $this->getMerchantFacade(),
        );
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Reader\MerchantUserReaderInterface
     */
    public function createMerchantUserReader(): MerchantUserReaderInterface
    {
        return new MerchantUserReader(
            $this->getConfig(),
            $this->getMerchantUserFacade(),
        );
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Saver\AclEntitySaverInterface
     */
    public function createAclEntitySaver(): AclEntitySaverInterface
    {
        return new AclEntitySaver(
            $this->getAclFacade(),
            $this->createAclMerchantPortalGenerator(),
            $this->createAclRoleSaver(),
            $this->createGroupAdder(),
            $this->createAclRoleAssignmentChecker(),
        );
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Synchronizer\AclEntitySynchronizerInterface
     */
    public function createAclEntitySynchronizer(): AclEntitySynchronizerInterface
    {
        return new AclEntitySynchronizer(
            $this->createMerchantReader(),
            $this->createMerchantUserReader(),
            $this->createAclEntitySaver(),
        );
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Filter\AclEntityRuleFilterInterface
     */
    public function createAclEntityRuleFilter(): AclEntityRuleFilterInterface
    {
        return new AclEntityRuleFilter(
            $this->getAclEntityFacade(),
        );
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Business\Checker\AclRoleAssignmentCheckerInterface
     */
    public function createAclRoleAssignmentChecker(): AclRoleAssignmentCheckerInterface
    {
        return new AclRoleAssignmentChecker($this->getAclFacade());
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Dependency\Facade\AclMerchantPortalToMerchantFacadeInterface
     */
    public function getMerchantFacade(): AclMerchantPortalToMerchantFacadeInterface
    {
        return $this->getProvidedDependency(AclMerchantPortalDependencyProvider::FACADE_MERCHANT);
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Dependency\Facade\AclMerchantPortalToMerchantUserFacadeInterface
     */
    public function getMerchantUserFacade(): AclMerchantPortalToMerchantUserFacadeInterface
    {
        return $this->getProvidedDependency(AclMerchantPortalDependencyProvider::FACADE_MERCHANT_USER);
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Dependency\Facade\AclMerchantPortalToAclFacadeInterface
     */
    public function getAclFacade(): AclMerchantPortalToAclFacadeInterface
    {
        return $this->getProvidedDependency(AclMerchantPortalDependencyProvider::FACADE_ACL);
    }

    /**
     * @return \Spryker\Zed\AclMerchantPortal\Dependency\Facade\AclMerchantPortalToAclEntityFacadeInterface
     */
    public function getAclEntityFacade(): AclMerchantPortalToAclEntityFacadeInterface
    {
        return $this->getProvidedDependency(AclMerchantPortalDependencyProvider::FACADE_ACL_ENTITY);
    }

    /**
     * @return list<\Spryker\Zed\AclMerchantPortalExtension\Dependency\Plugin\MerchantAclRuleExpanderPluginInterface>
     */
    public function getMerchantAclRuleExpanderPlugins(): array
    {
        return $this->getProvidedDependency(AclMerchantPortalDependencyProvider::PLUGINS_MERCHANT_ACL_RULE_EXPANDER);
    }

    /**
     * @return list<\Spryker\Zed\AclMerchantPortalExtension\Dependency\Plugin\MerchantAclEntityRuleExpanderPluginInterface>
     */
    public function getMerchantAclEntityRuleExpanderPlugins(): array
    {
        return $this->getProvidedDependency(AclMerchantPortalDependencyProvider::PLUGINS_MERCHANT_ACL_ENTITY_RULE_EXPANDER);
    }

    /**
     * @return list<\Spryker\Zed\AclMerchantPortalExtension\Dependency\Plugin\MerchantUserAclRuleExpanderPluginInterface>
     */
    public function getMerchantUserAclRuleExpanderPlugins(): array
    {
        return $this->getProvidedDependency(AclMerchantPortalDependencyProvider::PLUGINS_MERCHANT_USER_ACL_RULE_EXPANDER);
    }

    /**
     * @return list<\Spryker\Zed\AclMerchantPortalExtension\Dependency\Plugin\MerchantUserAclEntityRuleExpanderPluginInterface>
     */
    public function getMerchantUserAclEntityRuleExpanderPlugins(): array
    {
        return $this->getProvidedDependency(AclMerchantPortalDependencyProvider::PLUGINS_MERCHANT_USER_ACL_ENTITY_RULE_EXPANDER);
    }

    /**
     * @return list<\Spryker\Zed\AclMerchantPortalExtension\Dependency\Plugin\AclEntityConfigurationExpanderPluginInterface>
     */
    public function getAclEntityConfigurationExpanderPlugins(): array
    {
        return $this->getProvidedDependency(AclMerchantPortalDependencyProvider::PLUGINS_ACL_ENTITY_CONFIGURATION_EXPANDER);
    }
}
