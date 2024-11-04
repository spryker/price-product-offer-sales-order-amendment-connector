<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CmsGui\Presentation;

use SprykerTest\Zed\CmsGui\CmsGuiPresentationTester;
use SprykerTest\Zed\CmsGui\PageObject\CmsGuiListPage;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CmsGui
 * @group Presentation
 * @group CmsGuiEditCest
 * Add your own group annotations below this line
 */
class CmsGuiEditCest
{
    /**
     * @param \SprykerTest\Zed\CmsGui\CmsGuiPresentationTester $i
     *
     * @return void
     */
    public function _before(CmsGuiPresentationTester $i): void
    {
        $i->amZed();
        $i->amLoggedInUser();
    }

    /**
     * @param \SprykerTest\Zed\CmsGui\CmsGuiPresentationTester $i
     *
     * @return void
     */
    public function breadcrumbIsVisible(CmsGuiPresentationTester $i): void
    {
        $i->amOnPage(CmsGuiListPage::URL);
        $i->clickDataTableLinkInDropDownOfButton('Edit', 'Page');
        $i->seeBreadcrumbNavigation('Content / Pages / Edit CMS Page');
    }
}
