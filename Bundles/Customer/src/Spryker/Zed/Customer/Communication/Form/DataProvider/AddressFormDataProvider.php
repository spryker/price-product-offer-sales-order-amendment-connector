<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Customer\Communication\Form\DataProvider;

use Spryker\Zed\Customer\Communication\Form\AddressForm;
use Spryker\Zed\Customer\Dependency\Facade\CustomerToCountryInterface;
use Spryker\Zed\Customer\Dependency\Facade\CustomerToStoreFacadeInterface;
use Spryker\Zed\Customer\Persistence\CustomerQueryContainerInterface;

class AddressFormDataProvider extends AbstractCustomerDataProvider
{
    /**
     * @var \Spryker\Zed\Customer\Dependency\Facade\CustomerToCountryInterface
     */
    protected $countryFacade;

    /**
     * @var \Spryker\Zed\Customer\Persistence\CustomerQueryContainerInterface
     */
    protected $customerQueryContainer;

    /**
     * @var \Spryker\Zed\Customer\Dependency\Facade\CustomerToStoreFacadeInterface
     */
    protected $storeFacade;

    /**
     * @param \Spryker\Zed\Customer\Dependency\Facade\CustomerToCountryInterface $countryFacade
     * @param \Spryker\Zed\Customer\Persistence\CustomerQueryContainerInterface $customerQueryContainer
     * @param \Spryker\Zed\Customer\Dependency\Facade\CustomerToStoreFacadeInterface $storeFacade
     */
    public function __construct(
        CustomerToCountryInterface $countryFacade,
        CustomerQueryContainerInterface $customerQueryContainer,
        CustomerToStoreFacadeInterface $storeFacade
    ) {
        $this->countryFacade = $countryFacade;
        $this->customerQueryContainer = $customerQueryContainer;
        $this->storeFacade = $storeFacade;
    }

    /**
     * @param int|null $idCustomerAddress
     *
     * @return array<string, mixed>
     */
    public function getData($idCustomerAddress = null)
    {
        if ($idCustomerAddress === null) {
            return [];
        }

        $addressEntity = $this->customerQueryContainer->queryAddress($idCustomerAddress)->findOne();

        return $addressEntity->toArray();
    }

    /**
     * @return array<string, mixed>
     */
    public function getOptions()
    {
        return [
            AddressForm::OPTION_SALUTATION_CHOICES => $this->getSalutationChoices(),
            AddressForm::OPTION_COUNTRY_CHOICES => $this->getCountryChoices(),
        ];
    }

    /**
     * @return array<int|string, string|null>
     */
    protected function getCountryChoices(): array
    {
        $countries = $this->countryFacade->getAvailableCountries()
            ->getCountries()
            ->getIterator()
            ->getArrayCopy();

        return $this->getCountryNamesIndexedByCountryId($countries);
    }

    /**
     * @param array<\Generated\Shared\Transfer\CountryTransfer> $countries
     *
     * @return array<int|string, string|null>
     */
    protected function getCountryNamesIndexedByCountryId(array $countries): array
    {
        $result = [];

        foreach ($countries as $countryTransfer) {
            $result[$countryTransfer->getIdCountry()] = $countryTransfer->getName();
        }

        return $result;
    }
}
