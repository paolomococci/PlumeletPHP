<?php

declare(strict_types=1); // Enforce strict type checking

namespace App\Util\Helpers;

use App\Util\Mailers\MailBuilder;

/**
 * PasswordResetHelper
 */
final class PasswordResetHelper
{
    /**
     * Send a password-reset mail to the given address.
     *
     * @param MailBuilder $builder      An already-constructed MailBuilder.
     * @param string      $recipient    Email address of the user.
     *
     * @return string The generated pass-phrase.
     */
    public static function sendResetMail(MailBuilder $builder, string $recipient): string
    {
        // 1. Generate an 32-character hexadecimal pass-phrase.
        $passphrase = bin2hex(random_bytes(16));

        // 2. Build the body.
        $body = "Here is your temporary passphrase: {$passphrase}";

        // 3. Wire everything together.
        $builder
            ->to($recipient)
            ->subject('your password reset passphrase')
            ->body($body)
            ->send();

        // Optional.
        return $passphrase;
    }
}
