<?php

declare (strict_types = 1); // Enforce strict type checking

namespace App\Config;

use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

final class ErrorsDev
{
    public static function initialize(): void
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        set_exception_handler(fn(Throwable $th) => self::handleException($th));
    }

    public static function handleException(Throwable $th): void
    {
        http_response_code(500);

        // Error log con Monolog
        $logger = new Logger('app');
        $logger->pushHandler(new StreamHandler(APP_ROOT_DIR . '/stores/logs/app_dev.log', Level::Error));
        $logger->error($th); // $th, it's already a Psr\Log\LogRecord

        $whoops = new Run;
        $whoops->pushHandler(new PrettyPageHandler);
        $whoops->register();

        throw $th;
    }
}
