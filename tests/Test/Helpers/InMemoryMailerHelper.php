<?php

namespace Test\Helpers;

use App\Backend\Models\Mail;
use App\Util\Interfaces\MailerInterface;

/**
 * InMemoryMailerHelper
 *
 * A simple in-memory mailer used exclusively in tests.
 * It implements MailerInterface and simply keeps a reference to the
 * last Mail instance that was sent.
 */
final class InMemoryMailerHelper implements MailerInterface
{
    /** @var Mail|null The last mail instance that was passed to */
    public ?Mail $sentMail = null;

    /**
     * send
     *
     * Store the mail for later inspection.
     *
     * @param  mixed $mail  The mail that the production code is attempting to deliver.
     *                      In a real implementation this method would actually send
     *                      the mail, but in tests we merely keep a reference to it.
     * @return void
     */
    public function send(Mail $mail): void
    {
        $this->sentMail = $mail;
    }
}
