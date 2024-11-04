<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Mail\Business\Model\Renderer;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\LocaleTransfer;
use Generated\Shared\Transfer\MailTemplateTransfer;
use Generated\Shared\Transfer\MailTransfer;
use Spryker\Zed\Glossary\Communication\Plugin\TwigTranslatorPlugin;
use Spryker\Zed\Mail\Business\Model\Renderer\TwigRenderer;
use Spryker\Zed\Mail\Dependency\Renderer\MailToRendererBridge;
use Spryker\Zed\Mail\Dependency\Renderer\MailToRendererInterface;
use Twig\Environment;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Mail
 * @group Business
 * @group Model
 * @group Renderer
 * @group TwigRendererTest
 * Add your own group annotations below this line
 *
 * @property \SprykerTest\Zed\Mail\MailBusinessTester $tester
 */
class TwigRendererTest extends Unit
{
    /**
     * @var int
     */
    public const INDEX_OF_TEMPLATE_TEXT = 0;

    /**
     * @var int
     */
    public const INDEX_OF_TEMPLATE_HTML = 1;

    /**
     * @return void
     */
    public function testHydrateMailCallsTwigsRenderMethodWithTextTemplate(): void
    {
        $mailTransfer = $this->getMailTransfer();
        $twigRenderer = new TwigRenderer(
            $this->getTwigEnvironmentMock(),
            $this->tester->getLocaleFacade(),
            $this->tester->getModuleConfig(),
        );
        $twigRenderer->render($mailTransfer);

        $mailTemplateTextTransfer = $mailTransfer->getTemplates()[static::INDEX_OF_TEMPLATE_TEXT];
        $this->assertSame('TextTemplate', $mailTemplateTextTransfer->getContent());
    }

    /**
     * @return void
     */
    public function testHydrateMailCallsTwigsRenderMethodWithHtmlTemplate(): void
    {
        $mailTransfer = $this->getMailTransfer();
        $twigRenderer = new TwigRenderer(
            $this->getTwigEnvironmentMock(),
            $this->tester->getLocaleFacade(),
            $this->tester->getModuleConfig(),
        );
        $twigRenderer->render($mailTransfer);

        $mailTemplateHtmlTransfer = $mailTransfer->getTemplates()[static::INDEX_OF_TEMPLATE_HTML];
        $this->assertSame('HtmlTemplate', $mailTemplateHtmlTransfer->getContent());
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Mail\Dependency\Renderer\MailToRendererInterface
     */
    protected function getTwigEnvironmentMock(): MailToRendererInterface
    {
        $twigEnvironmentMock = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->onlyMethods(['render', 'getExtension'])->getMock();
        $twigEnvironmentMock->expects($this->once())->method('getExtension')->with(TwigTranslatorPlugin::class)->willReturn(new TwigTranslatorPlugin());
        $twigEnvironmentMock
            ->expects($this->exactly(2))
            ->method('render')
            ->willReturnOnConsecutiveCalls('TextTemplate', 'HtmlTemplate');

        $rendererBridge = new MailToRendererBridge($twigEnvironmentMock);

        return $rendererBridge;
    }

    /**
     * @return \Generated\Shared\Transfer\MailTransfer
     */
    protected function getMailTransfer(): MailTransfer
    {
        $mailTransfer = new MailTransfer();
        $mailTransfer->addTemplate($this->getMailTemplateTransferText());
        $mailTransfer->addTemplate($this->getMailTemplateTransferHtml());

        $localeTransfer = new LocaleTransfer();
        $localeTransfer->setLocaleName('en_US');
        $mailTransfer->setLocale($localeTransfer);

        return $mailTransfer;
    }

    /**
     * @return \Generated\Shared\Transfer\MailTemplateTransfer
     */
    protected function getMailTemplateTransferText(): MailTemplateTransfer
    {
        return $this->getMailTemplateTransfer(false);
    }

    /**
     * @return \Generated\Shared\Transfer\MailTemplateTransfer
     */
    protected function getMailTemplateTransferHtml(): MailTemplateTransfer
    {
        return $this->getMailTemplateTransfer(true);
    }

    /**
     * @param bool $isHtml
     *
     * @return \Generated\Shared\Transfer\MailTemplateTransfer
     */
    protected function getMailTemplateTransfer(bool $isHtml): MailTemplateTransfer
    {
        $mailTemplateTransfer = new MailTemplateTransfer();
        $mailTemplateTransfer->setIsHtml($isHtml);

        return $mailTemplateTransfer;
    }
}
