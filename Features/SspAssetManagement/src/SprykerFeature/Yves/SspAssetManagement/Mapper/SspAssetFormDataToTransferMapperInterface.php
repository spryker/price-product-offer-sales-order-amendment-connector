<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerFeature\Yves\SspAssetManagement\Mapper;

use Generated\Shared\Transfer\SspAssetTransfer;
use Symfony\Component\Form\FormInterface;

interface SspAssetFormDataToTransferMapperInterface
{
    /**
     * @param \Symfony\Component\Form\FormInterface $sspAssetForm
     * @param \Generated\Shared\Transfer\SspAssetTransfer $sspAssetTransfer
     *
     * @return \Generated\Shared\Transfer\SspAssetTransfer
     */
    public function mapFormDataToSspAssetTransfer(FormInterface $sspAssetForm, SspAssetTransfer $sspAssetTransfer): SspAssetTransfer;
}
