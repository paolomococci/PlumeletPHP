<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App\Backend\Models;

/**
 * Mail
 *
 * This simple data-transfer object holds the three core
 * properties that define an e-mail message: the recipient,
 * the subject line, and the body text.
 */
final class Mail
{
    /**
     * __construct
     *
     * Parameters are declared as public typed properties
     * (a PHP 8.0 feature). This automatically creates
     * corresponding public properties on the class.
     *
     * @param string $to      The e-mail address of the recipient.
     * @param string $subject The subject line of the message.
     * @param string $body    The plain-text body of the message.
     *
     * @return void
     */
    public function __construct(
        public string $to,
        public string $subject,
        public string $body,
    ) {}
}
