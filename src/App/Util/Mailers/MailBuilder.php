<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Util\Mailers;

use App\Backend\Models\Mail;
use App\Util\Interfaces\MailerInterface;

/**
 * MailBuilder
 *
 * A tiny, fluent helper that builds a Mail instance
 * and hands it off to an injected MailerInterface.
 *
 * Usage:
 * $passphrase = bin2hex(random_bytes(16));
 * MailBuilder::create($mailer)
 *      ->to('john.doe@sample.local')
 *      ->subject('your password reset passphrase')
 *      ->body("your password reset passphrase")
 *      ->send();
 *
 */
final class MailBuilder
{
    /** @var MailerInterface */
    private MailerInterface $mailer;

    /** @var Mail */
    private Mail $mail;

    /**
     * __construct
     *
     * @param  mixed $mailer
     * @return void
     */
    private function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
        // Start with an empty Mail; setters will populate it.
        $this->mail = new Mail(to: '', subject: '', body: '');
    }

    /**
     * create
     *
     * Static factory, keeps construction simple.
     *
     * @param  MailerInterface $mailer
     * @return self
     */
    public static function create(MailerInterface $mailer): self
    {
        return new self($mailer);
    }

    /** Fluent setters. */

    /**
     * to
     *
     * @param  string $to
     * @return self
     */
    public function to(string $to): self
    {
        $this->mail->to = $to;
        return $this;
    }

    /**
     * subject
     *
     * @param  string $subject
     * @return self
     */
    public function subject(string $subject): self
    {
        $this->mail->subject = $subject;
        return $this;
    }

    /**
     * body
     *
     * @param  string $body
     * @return self
     */
    public function body(string $body): self
    {
        $this->mail->body = $body;
        return $this;
    }

    /**
     * send
     * 
     * Final action.
     *
     * @return void
     */
    public function send(): void
    {
        $this->mailer->send($this->mail);
    }
}
