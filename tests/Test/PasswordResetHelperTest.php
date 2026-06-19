<?php

declare(strict_types=1); // Enforce strict type checking

/**
 * Production classes we are exercising.
 */

use App\Util\Helpers\PasswordResetHelper; // The helper under test.
use App\Util\Mailers\MailBuilder;         // Fluent builder.
use Test\Helpers\InMemoryMailerHelper;

/**
 * 2. The test generates a deterministic 16-byte (32-hex-char) pass-phrase
 *      that will appear in the body of the password-reset e-mail.
 *      Declaring it here keeps the closure that follows tidy.
 */
$passphrase = bin2hex(random_bytes(16));

/**
 * 3. Pest test: call PasswordResetHelper::sendResetMail() and assert
 *      everything that matters.
 */
it('generates a 32-character hex pass-phrase, builds the body, and sends the mail', function () use ($passphrase) {
    /**
     * Instantiate the in-memory mailer.  The builder will hand it the
     * ail it builds, and the double will record it.
     */
    $mailer = new InMemoryMailerHelper();

    /**
     * Create a MailBuilder that will use the in-memory mailer.
     */
    $builder = MailBuilder::create($mailer);

    /**
     * Call the helper - it will return the newly-generated pass-phrase.
     */
    $recipient           = 'john.doe@sample.local';
    $generatedPassphrase = PasswordResetHelper::sendResetMail($builder, $recipient);

    /**
     * Assert that the pass-phrase is a 32-character hex string.
     */
    expect($generatedPassphrase)
        ->toBeString()
        ->and(strlen($generatedPassphrase))->toBe(32)
        ->and((bool) preg_match('/^[0-9a-f]{32}$/', $generatedPassphrase))->toBeTrue();

    /**
     * Assert that the pass-phrase is a 32-character hex string, 
     * maintaining greater consistency with preg_match().
     */
    expect($generatedPassphrase)
        ->toBeString()
        ->and(strlen($generatedPassphrase))->toBe(32)
        ->and(preg_match('/^[0-9a-f]{32}$/', $generatedPassphrase))->toBe(1);

    /**
     * We should have captured a mail - the builder delegated to our
     * in-memory mailer.  If it's still null something went wrong.
     */
    expect($mailer->sentMail)->not->toBeNull();

    /**
     * Verify that the captured e-mail has the correct recipients,
     * subject, and that its body contains the generated pass-phrase.
     */
    expect($mailer->sentMail->to)
        ->toBe($recipient)
        ->and($mailer->sentMail->subject)
        ->toBe('your password reset passphrase')
        ->and($mailer->sentMail->body)
        ->toContain($generatedPassphrase);
});
