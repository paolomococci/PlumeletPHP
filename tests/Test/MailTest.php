<?php

declare(strict_types=1); // Enforce strict type checking

use App\Backend\Models\Mail;

it('creates a Mail object with the provided properties', function () {
    // Generate an 32-character hexadecimal pass-phrase.
    $passphrase = bin2hex(random_bytes(16));
    $mail       = new Mail(
        'john.doe@sample.local',
        'your password reset passphrase',
        "Here is your temporary passphrase: {$passphrase}"
    );

    expect($mail->to)->toBe('john.doe@sample.local')
        ->and($mail->subject)->toBe('your password reset passphrase')
        ->and($mail->body)->toBe("Here is your temporary passphrase: {$passphrase}");
});
