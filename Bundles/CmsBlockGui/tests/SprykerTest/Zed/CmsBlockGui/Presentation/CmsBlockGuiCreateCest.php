<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CmsBlockGui\Presentation;

use SprykerTest\Zed\CmsBlockGui\CmsBlockGuiPresentationTester;
use SprykerTest\Zed\CmsBlockGui\PageObject\CmsBlockGuiCreatePage;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CmsBlockGui
 * @group Presentation
 * @group CmsBlockGuiCreateCest
 * Add your own group annotations below this line
 */
class CmsBlockGuiCreateCest
{
    /**
     * @param \SprykerTest\Zed\CmsBlockGui\CmsBlockGuiPresentationTester $i
     *
     * @return void
     */
    public function _before(CmsBlockGuiPresentationTester $i): void
    {
        $i->amZed();
        $i->amLoggedInUser();
    }

    /**
     * @param \SprykerTest\Zed\CmsBlockGui\CmsBlockGuiPresentationTester $i
     *
     * @return void
     */
    public function testICanCreateCmsBlock(CmsBlockGuiPresentationTester $i): void
    {
        $i->amOnPage(CmsBlockGuiCreatePage::URL);
        $i->seeBreadcrumbNavigation('Content / Blocks / Create new CMS Block');
        $i->fillField(CmsBlockGuiCreatePage::FORM_FIELD_NAME_KEY, CmsBlockGuiCreatePage::FORM_FIELD_NAME_VALUE);

        $i->click(CmsBlockGuiCreatePage::FORM_SUBMIT_BUTTON);

        $i->seeInPageSource(CmsBlockGuiCreatePage::SUCCESS_MESSAGE);
    }
}
