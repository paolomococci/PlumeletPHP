<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App\Config;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Throwable;

final class ErrorsPro
{
    public static function initialize(): void
    {
        ini_set('display_errors', 0);
        // error_reporting(E_ALL); // optional
        set_exception_handler(fn(Throwable $th) => self::handleException($th));
    }

    public static function handleException(Throwable $th): void
    {
        http_response_code(500);

        // Error log con Monolog
        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler(APP_ROOT_DIR . '/stores/logs/app_pro.log', Level::Critical));
        $logger->error($th); // $th, it's already a Psr\Log\LogRecord

        require dirname(__DIR__, 1) . '/Frontend/Views/500.html';
        exit;
    }
}
