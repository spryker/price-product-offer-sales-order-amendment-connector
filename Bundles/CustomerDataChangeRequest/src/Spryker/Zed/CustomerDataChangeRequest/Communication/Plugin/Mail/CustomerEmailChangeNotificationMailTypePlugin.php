<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomerDataChangeRequest\Communication\Plugin\Mail;

use Generated\Shared\Transfer\MailRecipientTransfer;
use Generated\Shared\Transfer\MailTemplateTransfer;
use Generated\Shared\Transfer\MailTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\MailExtension\Dependency\Plugin\MailTypeBuilderPluginInterface;

/**
 * @method \Spryker\Zed\CustomerDataChangeRequest\CustomerDataChangeRequestConfig getConfig()
 * @method \Spryker\Zed\CustomerDataChangeRequest\Communication\CustomerDataChangeRequestCommunicationFactory getFactory()
 * @method \Spryker\Zed\CustomerDataChangeRequest\Business\CustomerDataChangeRequestFacadeInterface getFacade()
 */
class CustomerEmailChangeNotificationMailTypePlugin extends AbstractPlugin implements MailTypeBuilderPluginInterface
{
    /**
     * @var string
     */
    protected const MAIL_TYPE = 'CUSTOMER_EMAIL_CHANGE_NOTIFICATION_MAIL';

    /**
     * @var string
     */
    protected const MAIL_TEMPLATE_HTML = 'CustomerDataChangeRequest/mail/customer_email_change_notification.html.twig';

    /**
     * @var string
     */
    protected const MAIL_TEMPLATE_TEXT = 'CustomerDataChangeRequest/mail/customer_email_change_notification.text.twig';

    /**
     * @var string
     */
    protected const GLOSSARY_KEY_MAIL_SUBJECT = 'mail.customer.customer_email_change_notification.subject';

    /**
     * {@inheritDoc}
     *  - Return the name of mail for customer change notification mail.
     *
     * @api
     *
     * @return string
     */
    public function getName(): string
    {
        return static::MAIL_TYPE;
    }

    /**
     * {@inheritDoc}
     *  - Builds the `MailTransfer` with data for a customer change notification mail.
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\MailTransfer $mailTransfer
     *
     * @return \Generated\Shared\Transfer\MailTransfer
     */
    public function build(MailTransfer $mailTransfer): MailTransfer
    {
        /** @var \Generated\Shared\Transfer\CustomerTransfer $customerTransfer */
        $customerTransfer = $mailTransfer->getCustomerOrFail();

        return $mailTransfer
            ->setSubject(static::GLOSSARY_KEY_MAIL_SUBJECT)
            ->addTemplate(
                (new MailTemplateTransfer())
                    ->setName(static::MAIL_TEMPLATE_HTML)
                    ->setIsHtml(true),
            )
            ->addTemplate(
                (new MailTemplateTransfer())
                    ->setName(static::MAIL_TEMPLATE_TEXT)
                    ->setIsHtml(false),
            )
            ->addRecipient(
                (new MailRecipientTransfer())
                    ->setEmail($customerTransfer->getEmailOrFail())
                    ->setName(sprintf('%s %s', $customerTransfer->getFirstName(), $customerTransfer->getLastName())),
            );
    }
}
