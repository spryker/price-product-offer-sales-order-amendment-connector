<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\CategoryGui\Presentation;

use SprykerTest\Zed\CategoryGui\CategoryPresentationTester;
use SprykerTest\Zed\CategoryGui\PageObject\CategoryCreatePage;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group CategoryGui
 * @group Presentation
 * @group CategoryCreateCest
 * Add your own group annotations below this line
 */
class CategoryCreateCest
{
    /**
     * @param \SprykerTest\Zed\CategoryGui\CategoryPresentationTester $i
     *
     * @return void
     */
    public function _before(CategoryPresentationTester $i): void
    {
        $i->amZed();
        $i->amLoggedInUser();
    }

    /**
     * @param \SprykerTest\Zed\CategoryGui\CategoryPresentationTester $i
     *
     * @return void
     */
    public function testICanCreateCategory(CategoryPresentationTester $i): void
    {
        $i->amOnPage(CategoryCreatePage::URL);
        $i->seeBreadcrumbNavigation('Catalog / Category / Create category');
        $category = CategoryCreatePage::getCategorySelectorsWithValues(CategoryCreatePage::CATEGORY_A);
        $i->fillField(CategoryCreatePage::FORM_FIELD_CATEGORY_KEY, $category[CategoryCreatePage::FORM_FIELD_CATEGORY_KEY]);
        $i->selectOption(CategoryCreatePage::FORM_FIELD_CATEGORY_PARENT, $category[CategoryCreatePage::FORM_FIELD_CATEGORY_PARENT]);
        $i->selectOption(CategoryCreatePage::FORM_FIELD_CATEGORY_TEMPLATE, $category[CategoryCreatePage::FORM_FIELD_CATEGORY_TEMPLATE]);
        $localizedAttributes = $category['attributes'];

        foreach (CategoryCreatePage::CLOSED_IBOX_SELECTORS as $closedIboxSelector) {
            $i->click($closedIboxSelector);
        }

        foreach ($localizedAttributes as $attributes) {
            $this->fillAttributeFields($i, $attributes);
        }

        $i->click(CategoryCreatePage::FORM_SUBMIT_BUTTON);

        $i->seeInPageSource(CategoryCreatePage::SUCCESS_MESSAGE);
    }

    /**
     * @param \SprykerTest\Zed\CategoryGui\CategoryPresentationTester $i
     * @param array $attributes
     *
     * @return void
     */
    protected function fillAttributeFields(CategoryPresentationTester $i, array $attributes): void
    {
        foreach ($attributes as $selector => $value) {
            $i->waitForElementVisible(['name' => $selector]);
            $i->fillField(['name' => $selector], $value);
        }
    }
}
