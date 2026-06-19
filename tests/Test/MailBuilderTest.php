<?php

declare(strict_types=1); // Enforce strict type checking

/**
 * Imports of the production code we are exercising.
 */

use App\Backend\Models\Mail; // The entity that will be sent.
use App\Util\Mailers\MailBuilder;
use Test\Helpers\InMemoryMailerHelper;

/**
 * 2. Generate a deterministic "pass-phrase" that will be used in the
 *    body of the mail. Declaring it outside the closure keeps the test body clean.
 */
$passphrase = bin2hex(random_bytes(16));

/**
 * 3. Pest test: build a Mail via MailBuilder and confirm the mailer
 *      receives the correct instance.
 */
it('builds a Mail instance via a fluent API and delegates to the injected mailer', function () use ($passphrase) {
    /**
     * Instantiate the in-memory mailer. This object will capture
     * whatever MailBuilder hands it to send().
     */
    $mailer = new InMemoryMailerHelper();

    /**
     * Use MailBuilder's fluent API to populate a Mail instance
     * and call send(). The builder will delegate to our in-memory mailer.
     */
    MailBuilder::create($mailer)
        ->to('john.doe@sample.local')
        ->subject('your password reset passphrase')
        ->body("Here is your temporary passphrase: {$passphrase}")
        ->send();

    /**
     * Assertions ensure the mailer actually received a Mail instance.
     */
    expect($mailer->sentMail)->toBeInstanceOf(Mail::class);

    /**
     * Verify the properties of the captured Mail object.
     */
    expect($mailer->sentMail->to)
        ->toBe('john.doe@sample.local');

    expect($mailer->sentMail->subject)
        ->toBe('your password reset passphrase');

    expect($mailer->sentMail->body)
        ->toBe("Here is your temporary passphrase: {$passphrase}");
});
