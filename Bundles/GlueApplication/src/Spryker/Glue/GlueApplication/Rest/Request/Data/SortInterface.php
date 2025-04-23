<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\GlueApplication\Rest\Request\Data;

interface SortInterface
{
    /**
     * @var string
     */
    public const SORT_DESC = 'DESC';

    /**
     * @var string
     */
    public const SORT_ASC = 'ASC';

    /**
     * @return string
     */
    public function getField(): string;

    /**
     * @return string
     */
    public function getDirection(): string;
}
