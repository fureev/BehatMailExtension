<?php

namespace Staffim\Behat\MailExtension\Context;

use Behat\Behat\Context\SnippetAcceptingContext;
use Staffim\Behat\MailExtension\Account;
use Staffim\Behat\MailExtension\Exception\MailboxException;
use Staffim\Behat\MailExtension\Exception\MessageException;

/**
 * Additional steps for sending mail.
 */
// TODO This class may not works correctly for now. Fix it.
class ExtendedMailContext extends RawMailContext implements SnippetAcceptingContext
{
    /**
     * @When sign in to :smtpServer with :login and :password
     */
    // TODO Refactor: extract port to separate parameter, think about whole step.
    public function iSignInToSmtpServerWithLoginAndPassword($mailServer, $login, $password)
    {
        list($mailServer, $port) = explode(':', $mailServer) + [null, null];

        $smtpAccount = new Account($mailServer, $port, $login, $password);
        $this->getMailAgent()->setSmtpAccount($smtpAccount);
    }

    /**
     * @When sign out from mail server
     */
    public function iSignOutFromMailServer()
    {
        $this->getMailAgent()->disconnect();
    }

    /**
     * @When I remove mail from the server
     */
    public function iRemoveMailMessages()
    {
        // TODO Implement.
    }

    /**
     * @When I reply with :text
     * @When I reply with :text and attach :filename
     */
    public function iReplyWithMessage($text, $filename = null)
    {
        if ($filename) {
            $filename = $this->getMailAgentParameter('files_path') . $filename;
        }
        $replyMail = $this->getMailAgent()->createReplyMessage($this->getMail()->getRawMail(), $text, $filename);
        $this->getMailAgent()->send($replyMail);
    }

    /**
     * @When I send mail with :subject and :body to :address from :sender
     */
    public function iSendMail($subject, $body, $address, $sender)
    {
        $mail = $this->getMailAgent()->createMessage($subject, $body, $sender, $address);
        $this->getMailAgent()->send($mail);
    }

    /**
     * @When I send mail from file :filename
     */
    public function iSendMailFromFile($filename)
    {
        $mail = $this->getMailAgent()->createMessageFromFile($this->getMailAgentParameter('files_path') . $filename);
        $this->getMailAgent()->send($mail);
    }

    /**
     * @When I reply with message from file :filename
     */
    public function iReplyWithMessageFromFile($filename)
    {
        $mail = $this->getMailAgent()->createReplyMessageFromFile($this->getMail()->getRawMail(), $this->getMailAgentParameter('files_path') . $filename);
        $this->getMailAgent()->send($mail);
    }

    /**
     * @Then I should see :count messag(e|es)
     */
    public function iShouldSeeMailMessages($count)
    {
        $expectedCount = $count;
        $count         = $this->getMailAgent()->getMailbox()->getSize();

        if ($count !== (int) $expectedCount) {
            throw new MailboxException(sprintf('There are %s mail messages, not %s', $count, $expectedCount), $this->getMailAgent()->getMailbox());
        }
    }

    /**
     * @Then I should see attachment :text in mail message
     */
    public function iShouldSeeAttachment($text)
    {
        if (!$this->getMail()->findInAttachment($text)) {
            throw new MessageException(sprintf('Mail with "%s" in attachment file name not found.', $text), $this->getMail());
        }
    }
}
