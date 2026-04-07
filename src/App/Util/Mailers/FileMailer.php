<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Util\Mailers;

use App\Backend\Models\Mail;
use App\Util\Interfaces\MailerInterface;

/**
 * FileMailer
 *
 * A concrete implementation of MailerInterface that
 * writes e-mail messages to files instead of sending
 * them over SMTP. This is handy for development and
 * debugging purposes.
 *
 */
final class FileMailer implements MailerInterface
{
    /** @var string  Path where mail files will be stored. */
    private string $mailsDir = APP_ROOT_DIR . '/stores//mails';

    /**
     * __construct
     *
     * Ensures that the directory used for storing mail
     * files exists. If it doesn't, it is created with
     * recursive permission flags 0775.
     *
     * @return void
     */
    public function __construct()
    {
        // Create the folder if it doesn't already exist.
        if (! is_dir($this->mailsDir)) {
            // Only the owner can write; everyone else can read & enter the directory.
            mkdir($this->mailsDir, 0775, true);
        }
    }

    /**
     * send
     *
     * Writes the Mail object to a file.
     *
     * @param  Mail $mail
     *
     * @return void
     */
    public function send(Mail $mail): void
    {
        // Generate a unique filename based on the current timestamp
        // and a random uniqid() string to avoid collisions.
        $filename = sprintf(
            '%s/%s-%s.txt',
            $this->mailsDir,
            date('YmdHis'),
            uniqid()
        );

        // Build a plain-text representation of the e-mail.
        $content = <<<EOD
            To: {$mail->to}
            Subject: {$mail->subject}

            {$mail->body}
        EOD;

        // Persist the content to disk.
        file_put_contents($filename, $content);
    }
}
