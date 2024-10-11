<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Spryker Marketplace License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\MerchantProfile\Business;

use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;
use Spryker\Zed\MerchantProfile\Business\Expander\MerchantProfileMerchantExpander;
use Spryker\Zed\MerchantProfile\Business\Expander\MerchantProfileMerchantExpanderInterface;
use Spryker\Zed\MerchantProfile\Business\GlossaryKeyBuilder\MerchantProfileGlossaryKeyBuilder;
use Spryker\Zed\MerchantProfile\Business\GlossaryKeyBuilder\MerchantProfileGlossaryKeyBuilderInterface;
use Spryker\Zed\MerchantProfile\Business\MerchantDataOrder\MerchantDataOrderHydrate;
use Spryker\Zed\MerchantProfile\Business\MerchantProfile\MerchantProfileReader;
use Spryker\Zed\MerchantProfile\Business\MerchantProfile\MerchantProfileReaderInterface;
use Spryker\Zed\MerchantProfile\Business\MerchantProfile\MerchantProfileWriter;
use Spryker\Zed\MerchantProfile\Business\MerchantProfile\MerchantProfileWriterInterface;
use Spryker\Zed\MerchantProfile\Business\MerchantProfileAddress\MerchantProfileAddressWriter;
use Spryker\Zed\MerchantProfile\Business\MerchantProfileAddress\MerchantProfileAddressWriterInterface;
use Spryker\Zed\MerchantProfile\Business\MerchantProfileGlossary\MerchantProfileGlossaryWriter;
use Spryker\Zed\MerchantProfile\Business\MerchantProfileGlossary\MerchantProfileGlossaryWriterInterface;
use Spryker\Zed\MerchantProfile\Business\MerchantProfileQuote\MerchantProfileItemExpander;
use Spryker\Zed\MerchantProfile\Business\MerchantProfileQuote\MerchantProfileItemExpanderInterface;
use Spryker\Zed\MerchantProfile\Dependency\Facade\MerchantProfileToGlossaryFacadeInterface;
use Spryker\Zed\MerchantProfile\Dependency\Facade\MerchantProfileToLocaleFacadeInterface;
use Spryker\Zed\MerchantProfile\MerchantProfileDependencyProvider;

/**
 * @method \Spryker\Zed\MerchantProfile\Persistence\MerchantProfileRepositoryInterface getRepository()
 * @method \Spryker\Zed\MerchantProfile\Persistence\MerchantProfileEntityManagerInterface getEntityManager()
 * @method \Spryker\Zed\MerchantProfile\MerchantProfileConfig getConfig()
 */
class MerchantProfileBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \Spryker\Zed\MerchantProfile\Business\MerchantProfile\MerchantProfileWriterInterface
     */
    public function createMerchantProfileWriter(): MerchantProfileWriterInterface
    {
        return new MerchantProfileWriter(
            $this->getEntityManager(),
            $this->createMerchantProfileGlossaryWriter(),
            $this->createMerchantProfileAddressWriter(),
        );
    }

    /**
     * @return \Spryker\Zed\MerchantProfile\Business\MerchantProfile\MerchantProfileReaderInterface
     */
    public function createMerchantProfileReader(): MerchantProfileReaderInterface
    {
        return new MerchantProfileReader(
            $this->getRepository(),
        );
    }

    /**
     * @return \Spryker\Zed\MerchantProfile\Business\MerchantProfileGlossary\MerchantProfileGlossaryWriterInterface
     */
    public function createMerchantProfileGlossaryWriter(): MerchantProfileGlossaryWriterInterface
    {
        return new MerchantProfileGlossaryWriter(
            $this->getGlossaryFacade(),
            $this->getLocaleFacade(),
            $this->createMerchantProfileGlossaryKeyBuilder(),
        );
    }

    /**
     * @return \Spryker\Zed\MerchantProfile\Business\MerchantProfileAddress\MerchantProfileAddressWriterInterface
     */
    public function createMerchantProfileAddressWriter(): MerchantProfileAddressWriterInterface
    {
        return new MerchantProfileAddressWriter(
            $this->getEntityManager(),
        );
    }

    /**
     * @return \Spryker\Zed\MerchantProfile\Business\GlossaryKeyBuilder\MerchantProfileGlossaryKeyBuilderInterface
     */
    public function createMerchantProfileGlossaryKeyBuilder(): MerchantProfileGlossaryKeyBuilderInterface
    {
        return new MerchantProfileGlossaryKeyBuilder();
    }

    /**
     * @return \Spryker\Zed\MerchantProfile\Dependency\Facade\MerchantProfileToGlossaryFacadeInterface
     */
    public function getGlossaryFacade(): MerchantProfileToGlossaryFacadeInterface
    {
        return $this->getProvidedDependency(MerchantProfileDependencyProvider::FACADE_GLOSSARY);
    }

    /**
     * @return \Spryker\Zed\MerchantProfile\Dependency\Facade\MerchantProfileToLocaleFacadeInterface
     */
    public function getLocaleFacade(): MerchantProfileToLocaleFacadeInterface
    {
        return $this->getProvidedDependency(MerchantProfileDependencyProvider::FACADE_LOCALE);
    }

    /**
     * @return \Spryker\Zed\MerchantProfile\Business\MerchantDataOrder\MerchantDataOrderHydrate
     */
    public function createMerchantDataOrderHydrate(): MerchantDataOrderHydrate
    {
        return new MerchantDataOrderHydrate($this->getRepository());
    }

    /**
     * @return \Spryker\Zed\MerchantProfile\Business\Expander\MerchantProfileMerchantExpanderInterface
     */
    public function createMerchantProfileMerchantExpander(): MerchantProfileMerchantExpanderInterface
    {
        return new MerchantProfileMerchantExpander(
            $this->getRepository(),
            $this->getGlossaryFacade(),
            $this->getLocaleFacade(),
        );
    }

    /**
     * @return \Spryker\Zed\MerchantProfile\Business\MerchantProfileQuote\MerchantProfileItemExpanderInterface
     */
    public function createMerchantProfileItemExpander(): MerchantProfileItemExpanderInterface
    {
        return new MerchantProfileItemExpander($this->createMerchantProfileReader());
    }
}
