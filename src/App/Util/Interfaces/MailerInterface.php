<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Util\Interfaces;

use App\Backend\Models\Mail;

/**
 * MailerInterface
 *
 * Any concrete mailer must implement a single method:
 * send(), which accepts a Mail object and returns void.
 * This allows the rest of the application to work with
 * any mailer implementation without caring about the
 * underlying transport mechanism.
 */
interface MailerInterface
{
    /**
     * send
     *
     * @param  Mail $mail   The message to be sent.
     *
     * @return void
     */
    public function send(Mail $mail): void;
}
