<?php

declare (strict_types = 1); // Enforce strict type checking

/**
 * bootstrap.php
 *
 * 1. Load Composer's autoloader.
 * 2. Creates an instance of class `PlumeletPHP\App\Bootstrap`.
 * 3. Call `run()` to start the application.
 */

require __DIR__ . '/vendor/autoload.php'; // composer‑generated autoloader

use App\Bootstrap;
use Dotenv\Dotenv;
use App\Config\ErrorsDev;
use App\Config\ErrorsPro;

// Environment setup
define('APP_ROOT_DIR', __DIR__);
$dotenv = Dotenv::createImmutable(APP_ROOT_DIR);
$dotenv->load();
$environment = $_ENV['APP_ENV'] ?? 'pro';

$errorsMap = [
    'pro' => ErrorsPro::class, // default
    'dev' => ErrorsDev::class,
    // possono seguire altri eventuali ambienti
];

$errorsClass = $errorsMap[$environment] ?? ErrorsPro::class;
$errorsClass::initialize();

$app = new Bootstrap(
    environment: $environment,
    appRootDir: APP_ROOT_DIR
);

$app->run();
