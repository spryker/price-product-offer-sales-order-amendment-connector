<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Zed\SspAssetManagement\Communication\Form\DataProvider;

use ArrayObject;
use Generated\Shared\Transfer\CompanyBusinessUnitCriteriaFilterTransfer;
use Generated\Shared\Transfer\CompanyCriteriaFilterTransfer;
use Generated\Shared\Transfer\SspAssetConditionsTransfer;
use Generated\Shared\Transfer\SspAssetCriteriaTransfer;
use Generated\Shared\Transfer\SspAssetIncludeTransfer;
use Generated\Shared\Transfer\SspAssetTransfer;
use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Zed\Company\Business\CompanyFacadeInterface;
use Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitFacadeInterface;
use SprykerFeature\Zed\SspAssetManagement\Business\SspAssetManagementFacadeInterface;
use SprykerFeature\Zed\SspAssetManagement\Communication\Form\SspAssetForm;
use SprykerFeature\Zed\SspAssetManagement\SspAssetManagementConfig;

class SspAssetFormDataProvider implements SspAssetFormDataProviderInterface
{
    /**
     * @param \SprykerFeature\Zed\SspAssetManagement\Business\SspAssetManagementFacadeInterface $sspAssetManagementFacade
     * @param \SprykerFeature\Zed\SspAssetManagement\SspAssetManagementConfig $config
     * @param \Spryker\Zed\CompanyBusinessUnit\Business\CompanyBusinessUnitFacadeInterface $companyBusinessUnitFacade
     * @param \Spryker\Zed\Company\Business\CompanyFacadeInterface $companyFacade
     */
    public function __construct(
        protected SspAssetManagementFacadeInterface $sspAssetManagementFacade,
        protected SspAssetManagementConfig $config,
        protected CompanyBusinessUnitFacadeInterface $companyBusinessUnitFacade,
        protected CompanyFacadeInterface $companyFacade
    ) {
    }

    /**
     * @param int $sspAssetId
     *
     * @return \Generated\Shared\Transfer\SspAssetTransfer|null
     */
    public function getData(int $sspAssetId): ?SspAssetTransfer
    {
        $sspAssetCollectionTransfer = $this->sspAssetManagementFacade->getSspAssetCollection(
            (new SspAssetCriteriaTransfer())
                ->setSspAssetConditions(
                    (new SspAssetConditionsTransfer())
                        ->addIdSspAsset($sspAssetId),
                )
                ->setInclude(
                    (new SspAssetIncludeTransfer())
                        ->setWithCompanyBusinessUnit(true)
                        ->setWithAssignedBusinessUnits(true),
                ),
        );

        if ($sspAssetCollectionTransfer->getSspAssets()->count() === 0) {
            return null;
        }

        return $sspAssetCollectionTransfer->getSspAssets()->getIterator()->current();
    }

    /**
     * @param \Generated\Shared\Transfer\SspAssetTransfer $sspAssetTransfer
     *
     * @return array<string, mixed>
     */
    public function getOptions(SspAssetTransfer $sspAssetTransfer): array
    {
        $assignedBusinessUnits = $this->getAssignedBusinessUnits($sspAssetTransfer->getAssignments());
        $assignedCompanies = $this->getAssignedCompanies($sspAssetTransfer->getAssignments());

        $companyBusinessUnitOwnerTransfer = $sspAssetTransfer->getCompanyBusinessUnit();

        return [
            SspAssetForm::OPTION_ORIGINAL_IMAGE_URL => $this->getAssetImageUrl($sspAssetTransfer),
            SspAssetForm::OPTION_COMPANY_ASSIGMENT_OPTIONS => $assignedCompanies,
            SspAssetForm::OPTION_BUSINESS_UNIT_ASSIGMENT_OPTIONS => $assignedBusinessUnits,
            SspAssetForm::OPTION_STATUS_OPTIONS => array_flip($this->config->getAssetStatuses()),
            SspAssetForm::OPTION_BUSINESS_UNIT_OWNER => $companyBusinessUnitOwnerTransfer ? [$companyBusinessUnitOwnerTransfer->getNameOrFail() => $companyBusinessUnitOwnerTransfer->getIdCompanyBusinessUnitOrFail()] : [],
        ];
    }

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $submittedFormData
     *
     * @return array<string, mixed>
     */
    public function expandOptionsWithSubmittedData(array $options, array $submittedFormData): array
    {
        $assignedBusinessUnitIds = [];
        if (isset($submittedFormData[SspAssetForm::FIELD_ASSIGNED_BUSINESS_UNITS])) {
            $assignedBusinessUnitIds = array_map('intval', $submittedFormData[SspAssetForm::FIELD_ASSIGNED_BUSINESS_UNITS]);
        }

        $businessUnitOwnerId = null;
        if (isset($submittedFormData[SspAssetForm::FIELD_BUSINESS_UNIT_OWNER])) {
            $businessUnitOwnerId = $submittedFormData[SspAssetForm::FIELD_BUSINESS_UNIT_OWNER];
            $businessUnitOwnerId = in_array($businessUnitOwnerId, $assignedBusinessUnitIds) ? $businessUnitOwnerId : null;
        }

        $companyBusinessUnitCollectionTransfer = $this->companyBusinessUnitFacade->getCompanyBusinessUnitCollection(
            (new CompanyBusinessUnitCriteriaFilterTransfer())->setCompanyBusinessUnitIds($assignedBusinessUnitIds),
        );

        $assignedBusinessUnitOptions = [];
        foreach ($companyBusinessUnitCollectionTransfer->getCompanyBusinessUnits() as $companyBusinessUnitTransfer) {
            $assignedBusinessUnitOptions[$companyBusinessUnitTransfer->getNameOrFail()] = $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail();
        }

        $assignedCompanyIds = [];
        if (isset($submittedFormData[SspAssetForm::FIELD_ASSIGNED_COMPANIES])) {
            $assignedCompanyIds = array_map('intval', $submittedFormData[SspAssetForm::FIELD_ASSIGNED_COMPANIES]);
        }

        $companyCollectionTransfer = $this->companyFacade->getCompanyCollection(
            (new CompanyCriteriaFilterTransfer())->setCompanyIds($assignedCompanyIds),
        );

        $assignedCompanyOptions = [];
        foreach ($companyCollectionTransfer->getCompanies() as $companyTransfer) {
            $assignedCompanyOptions[$companyTransfer->getNameOrFail()] = $companyTransfer->getIdCompanyOrFail();
        }

        $expandedFormOptions = [
            SspAssetForm::OPTION_BUSINESS_UNIT_ASSIGMENT_OPTIONS => $assignedBusinessUnitOptions,
            SspAssetForm::OPTION_BUSINESS_UNIT_OWNER => $businessUnitOwnerId ? [array_flip($assignedBusinessUnitOptions)[$businessUnitOwnerId] => $businessUnitOwnerId] : [],
            SspAssetForm::OPTION_COMPANY_ASSIGMENT_OPTIONS => $assignedCompanyOptions,
        ];

        return array_merge($options, $expandedFormOptions);
    }

    /**
     * @param \Generated\Shared\Transfer\SspAssetTransfer $sspAssetTransfer
     *
     * @return string|null
     */
    public function getAssetImageUrl(SspAssetTransfer $sspAssetTransfer): ?string
    {
        if (!$sspAssetTransfer->getImage()) {
            return null;
        }

        return Url::generate('/ssp-asset-management/image', ['ssp-asset-reference' => $sspAssetTransfer->getReferenceOrFail()])->build();
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\SspAssetAssignmentTransfer> $sspAssetAssignmentTransfers
     *
     * @return array<string, int>
     */
    protected function getAssignedBusinessUnits(ArrayObject $sspAssetAssignmentTransfers): array
    {
        $assignedBusinessUnits = [];
        foreach ($sspAssetAssignmentTransfers as $sspAssetAssignmentTransfer) {
            $companyBusinessUnitTransfer = $sspAssetAssignmentTransfer->getCompanyBusinessUnit();
            if ($companyBusinessUnitTransfer) {
                $assignedBusinessUnits[$companyBusinessUnitTransfer->getNameOrFail()] = $companyBusinessUnitTransfer->getIdCompanyBusinessUnitOrFail();
            }
        }

        return $assignedBusinessUnits;
    }

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\SspAssetAssignmentTransfer> $sspAssetAssignmentTransfers
     *
     * @return array<string, int>
     */
    protected function getAssignedCompanies(ArrayObject $sspAssetAssignmentTransfers): array
    {
        $assignedCompanies = [];
        foreach ($sspAssetAssignmentTransfers as $sspAssetAssignmentTransfer) {
            $companyBusinessUnitTransfer = $sspAssetAssignmentTransfer->getCompanyBusinessUnit();
            if ($companyBusinessUnitTransfer) {
                $assignedCompanies[$companyBusinessUnitTransfer->getCompanyOrFail()->getNameOrFail()] = $companyBusinessUnitTransfer->getCompanyOrFail()->getIdCompanyOrFail();
            }
        }

        return $assignedCompanies;
    }
}
