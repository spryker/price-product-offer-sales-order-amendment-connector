<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Cms\Business;

use Codeception\Test\Unit;
use Orm\Zed\Cms\Persistence\SpyCmsGlossaryKeyMapping;
use Orm\Zed\Cms\Persistence\SpyCmsPage;
use Orm\Zed\Cms\Persistence\SpyCmsPageLocalizedAttributes;
use Orm\Zed\Glossary\Persistence\SpyGlossaryKey;
use Orm\Zed\Glossary\Persistence\SpyGlossaryTranslation;
use Propel\Runtime\Connection\ConnectionInterface;
use Spryker\Zed\Cms\Business\Mapping\CmsGlossaryKeyGeneratorInterface;
use Spryker\Zed\Cms\Business\Mapping\CmsGlossarySaverInterface;
use Spryker\Zed\Cms\Business\Page\CmsPageMapper;
use Spryker\Zed\Cms\Business\Page\CmsPageMapperInterface;
use Spryker\Zed\Cms\Business\Page\CmsPageUrlBuilderInterface;
use Spryker\Zed\Cms\Business\Page\Store\CmsPageStoreRelationReader;
use Spryker\Zed\Cms\Business\Page\Store\CmsPageStoreRelationReaderInterface;
use Spryker\Zed\Cms\Business\Page\Store\CmsPageStoreRelationWriterInterface;
use Spryker\Zed\Cms\Business\Template\TemplateManager;
use Spryker\Zed\Cms\Business\Template\TemplateManagerInterface;
use Spryker\Zed\Cms\Business\Template\TemplateReader;
use Spryker\Zed\Cms\Business\Template\TemplateReaderInterface;
use Spryker\Zed\Cms\CmsConfig;
use Spryker\Zed\Cms\Dependency\Facade\CmsToGlossaryFacadeInterface;
use Spryker\Zed\Cms\Dependency\Facade\CmsToLocaleFacadeInterface;
use Spryker\Zed\Cms\Dependency\Facade\CmsToTouchFacadeInterface;
use Spryker\Zed\Cms\Dependency\Facade\CmsToUrlFacadeInterface;
use Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface;
use Spryker\Zed\Cms\Persistence\CmsRepositoryInterface;

abstract class CmsMocks extends Unit
{
    /**
     * @param \Propel\Runtime\Connection\ConnectionInterface|null $propelConnectionMock
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface
     */
    protected function createCmsQueryContainerMock(?ConnectionInterface $propelConnectionMock = null): CmsQueryContainerInterface
    {
        $cmsQueryContainerMock = $this->getMockBuilder(CmsQueryContainerInterface::class)
            ->getMock();

        if ($propelConnectionMock === null) {
            $propelConnectionMock = $this->createPropelConnectionMock();
        }

        $cmsQueryContainerMock->method('getConnection')
            ->willReturn($propelConnectionMock);

        return $cmsQueryContainerMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Propel\Runtime\Connection\ConnectionInterface
     */
    protected function createPropelConnectionMock(): ConnectionInterface
    {
        return $this->getMockBuilder(ConnectionInterface::class)->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cms\Dependency\Facade\CmsToTouchFacadeInterface
     */
    protected function createTouchFacadeMock(): CmsToTouchFacadeInterface
    {
        return $this->getMockBuilder(CmsToTouchFacadeInterface::class)
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Orm\Zed\Cms\Persistence\SpyCmsPage
     */
    protected function createCmsPageEntityMock(): SpyCmsPage
    {
        return $this->getMockBuilder(SpyCmsPage::class)
            ->onlyMethods(['save', 'getVirtualColumn'])
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Orm\Zed\Cms\Persistence\SpyCmsPageLocalizedAttributes
     */
    protected function createCmsPageLocalizedAttributesEntityMock(): SpyCmsPageLocalizedAttributes
    {
        return $this->getMockBuilder(SpyCmsPageLocalizedAttributes::class)
            ->onlyMethods(['save'])
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cms\Dependency\Facade\CmsToUrlFacadeInterface
     */
    protected function createUrlFacadeMock(): CmsToUrlFacadeInterface
    {
        return $this->getMockBuilder(CmsToUrlFacadeInterface::class)
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cms\Business\Page\CmsPageUrlBuilderInterface
     */
    protected function createCmsPageUrlBuilderMock(): CmsPageUrlBuilderInterface
    {
        return $this->getMockBuilder(CmsPageUrlBuilderInterface::class)
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cms\Business\Mapping\CmsGlossarySaverInterface
     */
    protected function createCmsGlossarySaverMock(): CmsGlossarySaverInterface
    {
        return $this->getMockBuilder(CmsGlossarySaverInterface::class)
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cms\Business\Template\TemplateManagerInterface
     */
    protected function createTemplateManagerMock(): TemplateManagerInterface
    {
        return $this->getMockBuilder(TemplateManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTemplateById'])
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cms\CmsConfig
     */
    protected function createCmsConfigMock(): CmsConfig
    {
        return $this->getMockBuilder(CmsConfig::class)
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cms\Dependency\Facade\CmsToLocaleFacadeInterface
     */
    protected function createLocaleMock(): CmsToLocaleFacadeInterface
    {
        return $this->getMockBuilder(CmsToLocaleFacadeInterface::class)
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Orm\Zed\Cms\Persistence\SpyCmsGlossaryKeyMapping
     */
    protected function createGlossaryMappingEntityMock(): SpyCmsGlossaryKeyMapping
    {
        return $this->getMockBuilder(SpyCmsGlossaryKeyMapping::class)
            ->onlyMethods(['save'])
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Orm\Zed\Glossary\Persistence\SpyGlossaryKey
     */
    protected function createGlossaryKeyEntityMock(): SpyGlossaryKey
    {
        return $this->getMockBuilder(SpyGlossaryKey::class)
            ->onlyMethods(['save'])
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Orm\Zed\Glossary\Persistence\SpyGlossaryTranslation
     */
    protected function createGlossaryTranslationEntityMock(): SpyGlossaryTranslation
    {
        return $this->getMockBuilder(SpyGlossaryTranslation::class)
            ->onlyMethods(['save'])
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cms\Dependency\Facade\CmsToGlossaryFacadeInterface
     */
    protected function createGlossaryFacadeMock(): CmsToGlossaryFacadeInterface
    {
        return $this->getMockBuilder(CmsToGlossaryFacadeInterface::class)
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cms\Business\Mapping\CmsGlossaryKeyGeneratorInterface
     */
    protected function createCmsGlossaryKeyGeneratorMock(): CmsGlossaryKeyGeneratorInterface
    {
        return $this->getMockBuilder(CmsGlossaryKeyGeneratorInterface::class)
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cms\Business\Page\Store\CmsPageStoreRelationWriterInterface
     */
    protected function createCmsPageStoreRelationWriterMock(): CmsPageStoreRelationWriterInterface
    {
        return $this->getMockBuilder(CmsPageStoreRelationWriterInterface::class)
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cms\Persistence\CmsRepositoryInterface
     */
    protected function createCmsRepositoryMock(): CmsRepositoryInterface
    {
        return $this->getMockBuilder(CmsRepositoryInterface::class)
            ->getMock();
    }

    /**
     * @param \Spryker\Zed\Cms\Persistence\CmsQueryContainerInterface|\PHPUnit\Framework\MockObject\MockObject|null $cmsQueryContainerMock
     * @param \Spryker\Zed\Cms\Persistence\CmsRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject|null $cmsRepositoryMock
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cms\Business\Page\Store\CmsPageStoreRelationReaderInterface
     */
    protected function createCmsPageStoreRelationReaderMock(
        ?CmsQueryContainerInterface $cmsQueryContainerMock = null,
        ?CmsRepositoryInterface $cmsRepositoryMock = null
    ): CmsPageStoreRelationReaderInterface {
        if ($cmsQueryContainerMock === null) {
            $cmsQueryContainerMock = $this->createCmsQueryContainerMock();
        }

        if ($cmsRepositoryMock === null) {
            $cmsRepositoryMock = $this->createCmsRepositoryMock();
        }

        return $this->getMockBuilder(CmsPageStoreRelationReader::class)
            ->setConstructorArgs([$cmsQueryContainerMock, $cmsRepositoryMock])
            ->getMock();
    }

    /**
     * @param \Spryker\Zed\Cms\Business\Page\CmsPageUrlBuilderInterface|null $cmsPageUrlBuilderMock |\PHPUnit\Framework\MockObject\MockObject
     * @param \Spryker\Zed\Cms\Business\Page\Store\CmsPageStoreRelationReaderInterface|null $cmsPageStoreRelationReaderMock |\PHPUnit\Framework\MockObject\MockObject
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cms\Business\Page\CmsPageMapperInterface
     */
    protected function createCmsPageMapperMock(
        ?CmsPageUrlBuilderInterface $cmsPageUrlBuilderMock = null,
        ?CmsPageStoreRelationReaderInterface $cmsPageStoreRelationReaderMock = null
    ): CmsPageMapperInterface {
        if ($cmsPageUrlBuilderMock === null) {
            $cmsPageUrlBuilderMock = $this->createCmsPageUrlBuilderMock();
        }

        if ($cmsPageStoreRelationReaderMock === null) {
            $cmsPageStoreRelationReaderMock = $this->createCmsPageStoreRelationReaderMock();
        }

        return $this->getMockBuilder(CmsPageMapper::class)
            ->setConstructorArgs([$cmsPageUrlBuilderMock, $cmsPageStoreRelationReaderMock])
            ->onlyMethods([])
            ->getMock();
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Cms\Business\Template\TemplateReaderInterface
     */
    protected function createTemplateReaderMock(): TemplateReaderInterface
    {
        return $this->getMockBuilder(TemplateReader::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPlaceholdersByTemplatePath'])
            ->getMock();
    }
}
