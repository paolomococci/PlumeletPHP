<?php

declare(strict_types=1); // Enforce strict type checking

use App\Backend\Models\Mail;
use App\Util\Mailers\FileMailer;

$mailsDir = ''; // Declaration visible to static analysis tools.

/**
 * Environment constant.
 */
beforeAll(function () use (&$mailsDir) {
    $tmpRoot = sys_get_temp_dir() . '/app_root_' . uniqid();

    if (! defined('APP_ROOT_DIR')) {
        define('APP_ROOT_DIR', $tmpRoot);
    }

    // Mails folder path.
    $mailsDir = APP_ROOT_DIR . '/stores/mails';
});

/**
 * Cleaning each test, before running the tests.
 */
beforeEach(function () use (&$mailsDir) {
    if (is_dir($mailsDir)) {
        array_map('unlink', glob($mailsDir . '/*'));
        rmdir($mailsDir);
    }
});

/**
 * Test, creation of mails folder.
 */
it('creates the mails folder if it does not exist', function () use (&$mailsDir) {
    // The FileMailer constructor creates the tree.
    new FileMailer();

    expect(is_dir($mailsDir))->toBeTrue();
});

/**
 * Test, writing a Mail object to a text file.
 */
it('writes the Mail object into a new text file', function () use (&$mailsDir) {
    $mailer = new FileMailer();

    $mail = new Mail(
        to: 'alice.doe@sample.local',
        subject: 'Testing',
        body: 'This is a test message.'
    );

    /* -----------  (optional) capture console output  ----------- */
    $devOutput = null;
    ob_start();
    $mailer->send($mail);
    $devOutput = ob_get_clean();

    /* -----------  verify that a file has been created  ----------- */
    $files = glob($mailsDir . '/*.txt');

    expect($files)->toBeArray()
        ->and(count($files))->toBe(1);

    $content = file_get_contents($files[0]);

    expect($content)
        ->toContain('To: alice.doe@sample.local')
        ->and($content)->toContain('Subject: Testing')
        ->and($content)->toContain('This is a test message.');
});
