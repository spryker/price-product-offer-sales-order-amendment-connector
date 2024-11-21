<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\Customer;

use Codeception\Actor;
use Codeception\Stub;
use Spryker\Client\Session\SessionClientInterface;

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(\SprykerTest\Client\Customer\PHPMD)
 */
class CustomerClientTester extends Actor
{
    use _generated\CustomerClientTesterActions;

    /**
     * @param array $returnedValues
     *
     * @return \Spryker\Client\Session\SessionClientInterface
     */
    public function getSessionClientMock(array $returnedValues = []): SessionClientInterface
    {
        return Stub::makeEmpty(SessionClientInterface::class, [
            'get' => function ($key) use (&$returnedValues) {
                return $returnedValues[$key] ?? null;
            },
        ]);
    }
}
