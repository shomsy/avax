<?php

/** @noinspection PhpUnhandledExceptionInspection */
declare(strict_types=1);

use Avax\Container\Containers\DependencyInjector;

require_once __DIR__ . '/vendor/autoload.php';

try {
    // Instantiate the Dependency Injection Container
    $container = new DependencyInjector();

    // Register all configured service providers
    $container->register();

    // Boot lifecycle hooks and initialize bindings
    $container->boot();

    // Enable strict mode and validate bindings in a production environment only
    // This ensures that only explicitly registered services are resolvable
    // and that all dependencies are satisfied before the application starts
    if (getenv('APP_ENV') === 'production') {
        $container->enableStrictMode();
        $container->validateBindings(); // Fail-fast if any dependency is misconfigured
    }
} catch (Throwable $throwable) {
    // Set a timezone to Belgrade explicitly for logging
    date_default_timezone_set('Europe/Belgrade');

    // Define a log file path
    $logDir  = __DIR__ . '/storage/logs';
    $logFile = $logDir . '/' . date('d.m.Y') . '-bootstrap-error-logs.log';

    // Ensure directory exists
    if (! is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    // Build a log message with a timestamp
    $logMessage = sprintf(
        "[%s] BOOT ERROR: %s: %s in %s:%d\nTrace:\n%s\n\n",
        date('H:i:s'),
        $throwable::class,
        $throwable->getMessage(),
        $throwable->getFile(),
        $throwable->getLine(),
        $throwable->getTraceAsString()
    );

    // Write to a file
    file_put_contents($logFile, $logMessage, FILE_APPEND);

    // CLI fallback output
    if (PHP_SAPI === 'cli') {
        echo "ERROR: Application failed to bootstrap. See log: {$logFile}\n";
        exit(1);
    }

    // Emit generic HTML
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head><meta charset="UTF-8"><title>Application Error</title></head>
        <body style="background:#fafafa;font-family:sans-serif;text-align:center;padding:10vh;">
            <h1 style="font-size:2rem;color:#c00;">500 - Application Error</h1>
            <p>This is a software error. Please contact a software engineer as soon as possible.</p>
        </body>
        </html>
        HTML;

    exit(1);
}
