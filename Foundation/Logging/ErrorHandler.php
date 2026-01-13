<?php

declare(strict_types=1);

namespace Avax\Logging;

use Avax\Exceptions\ValidationException;
use Avax\HTTP\Response\JsonResponse;
use Avax\HTTP\Router\Routing\Exceptions\RouteNotFoundException;
use ErrorException;
use JetBrains\PhpStorm\NoReturn;
use Psr\Log\LoggerInterface;
use Spatie\Ignition\Ignition;
use Throwable;

/**
 * Centralized application error handler.
 *
 * Manages exceptions, errors, and shutdown handling consistently across application lifecycle.
 * Response rendered based on configured EXCEPTION_RESPONSE_FORMAT (.env).
 */
final readonly class ErrorHandler
{
    /**
     * Available rendering formats for exceptions.
     */
    private const string RENDER_FORMAT_IGNITION = 'ignition';

    private const string RENDER_FORMAT_JSON = 'json';

    /**
     * Constructor with property promotion for dependency injection.
     *
     * @param  LoggerInterface  $logger  Logger for error logging.
     */
    public function __construct(private LoggerInterface $logger) {}

    /**
     * Initializes global error handling for application.
     */
    public function initialize(): void
    {
        ob_start();
        set_exception_handler(callback: [$this, 'handle']);
        set_error_handler(callback: [$this, 'convertErrorToException']);
        register_shutdown_function(callback: [$this, 'handleShutdown']);
        $this->registerCliSignalHandlers();
    }

    /**
     * Registers CLI signal handlers for graceful shutdown.
     */
    private function registerCliSignalHandlers(): void
    {
        if (PHP_SAPI === 'cli' && function_exists(function: 'pcntl_signal')) {
            pcntl_signal(signal: SIGTERM, handler: fn () => $this->exitGracefully(signal: 'SIGTERM'));
            pcntl_signal(signal: SIGINT, handler: fn () => $this->exitGracefully(signal: 'SIGINT'));
        }
    }

    /**
     * Handles CLI graceful shutdown signals.
     */
    #[NoReturn]
    private function exitGracefully(string $signal): void
    {
        $this->logger->warning(
            message: "⚠️ {$signal} received – exiting gracefully.",
            context: ['file' => __FILE__, 'line' => __LINE__]
        );

        exit(0);
    }

    /**
     * Converts PHP errors to ErrorException instances.
     *
     * @throws ErrorException
     */
    public function convertErrorToException(
        int $severity,
        string $message,
        string $file,
        int $line
    ): never {
        throw new ErrorException(
            message : $message,
            code    : 0,
            severity: $severity,
            filename: $file,
            line    : $line
        );
    }

    /**
     * Handles fatal shutdown errors.
     *
     * @throws \JsonException
     */
    public function handleShutdown(): void
    {
        $error = error_get_last();

        if ($error !== null && isset($error['message'], $error['file'], $error['line'])) {
            $this->logger->error(
                message: "⚠️ Fatal error: {$error['message']}",
                context: ['file' => $error['file'], 'line' => $error['line'], 'type' => $error['type'] ?? E_ERROR]
            );

            $this->handle(
                throwable: new ErrorException(
                    message : $error['message'],
                    code    : 0,
                    severity: $error['type'] ?? E_ERROR,
                    filename: $error['file'],
                    line    : $error['line']
                )
            );
        }
    }

    /**
     * Handles uncaught exceptions globally.
     *
     * @throws \JsonException
     */
    public function handle(Throwable $throwable): void
    {
        try {
            $this->report(throwable: $throwable);

            match ($this->renderFormat()) {
                self::RENDER_FORMAT_JSON => $this->renderJson(throwable: $throwable),
                default => $this->renderIgnition(throwable: $throwable)
            };
        } catch (Throwable $e) {
            $this->logger->critical(
                message: "⚠️ Error handler crashed: {$e->getMessage()}",
                context: ['file' => $e->getFile(), 'line' => $e->getLine(), 'exception' => $e]
            );

            echo (new JsonResponse(status: 500, message: 'Internal Server Error'))->toJson();
        }

        if (ob_get_length() > 0) {
            ob_end_flush();
        } elseif (ob_get_status()) {
            ob_end_clean();
        }
    }

    /**
     * Reports throwable unless explicitly excluded.
     */
    private function report(Throwable $throwable): void
    {
        if ($throwable instanceof ValidationException) {
            return;
        }

        $level = $throwable instanceof RouteNotFoundException ? 'info' : 'error';

        $this->logger->log(
            level  : $level,
            message: $throwable->getMessage(),
            context: ['file' => $throwable->getFile(), 'line' => $throwable->getLine(), 'exception' => $throwable]
        );
    }

    /**
     * Determines an exception response format based on configuration.
     */
    private function renderFormat(): string
    {
        return env(key: 'EXCEPTION_RESPONSE_FORMAT', default: self::RENDER_FORMAT_IGNITION);
    }

    /**
     * Renders JSON formatted error response.
     *
     * @throws \JsonException
     */
    private function renderJson(Throwable $throwable): void
    {
        $response = $throwable instanceof ValidationException
            ? new JsonResponse(status: 422, message: 'Validation failed', data: $throwable->getErrors())
            : new JsonResponse(status: 500, message: 'An unexpected error occurred');

        if (! headers_sent()) {
            http_response_code(response_code: $response->status);
            header(header: 'Content-Type: application/json');
        }

        echo $response->toJson();
    }

    /**
     * Renders Ignition HTML formatted error response.
     */
    private function renderIgnition(Throwable $throwable): void
    {
        Ignition::make()
            ->shouldDisplayException(shouldDisplayException: true)
            ->setTheme(theme: 'dark')
            ->register()
            ->handleException(throwable: $throwable);
    }
}
