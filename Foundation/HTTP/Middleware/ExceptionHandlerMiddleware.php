<?php

declare(strict_types=1);

namespace Avax\HTTP\Middleware;

use Closure;
use Avax\HTTP\Response\ResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Spatie\Ignition\Ignition;
use Throwable;

/**
 * Middleware responsible for centralized exception handling using Spatie Ignition.
 *
 * Catches unhandled exceptions during request processing, logs them, and provides
 * appropriate user responses in either JSON format or via detailed Ignition pages,
 * based on the configured environment variable.
 */
readonly class ExceptionHandlerMiddleware
{
    /**
     * Supported exception response rendering formats.
     */
    private const string RENDER_FORMAT_IGNITION = 'ignition';

    private const string RENDER_FORMAT_JSON     = 'json';

    /**
     * @param LoggerInterface $logger          Logger instance for recording exceptions.
     * @param ResponseFactory $responseFactory Factory for generating HTTP responses.
     */
    public function __construct(
        private LoggerInterface $logger,
        private ResponseFactory $responseFactory,
    ) {}

    /**
     * Handles incoming HTTP requests and manages exception handling.
     *
     * Executes next middleware and catches unhandled exceptions, logs them, and renders
     * a response based on the configured rendering type (JSON or Ignition).
     *
     * @param ServerRequestInterface $serverRequest Incoming HTTP request instance.
     * @param Closure                $next          Next middleware handler closure.
     *
     * @return ResponseInterface HTTP response after handling exception.
     * @throws \JsonException
     * @throws \JsonException
     */
    public function handle(ServerRequestInterface $serverRequest, Closure $next) : ResponseInterface
    {
        try {
            // Invoke the next middleware or handler in the pipeline.
            return $next($serverRequest);
        } catch (Throwable $throwable) {
            // Log exception details for further analysis.
            $this->logger->error(
                message: 'Unhandled exception during request processing',
                context: ['exception' => $throwable]
            );

            // Determine a response rendering format based on environment configuration.
            return match ($this->renderFormat()) {
                self::RENDER_FORMAT_JSON => $this->renderJsonResponse(throwable: $throwable),
                default                  => $this->renderIgnitionResponse(throwable: $throwable),
            };
        }
    }

    /**
     * Retrieves configured exception rendering format from environment variables.
     *
     * Supported values:
     * - ignition: Renders exception using Ignition as detailed HTML.
     * - json: Returns JSON structured error response.
     *
     * @return string Configured rendering format.
     */
    private function renderFormat() : string
    {
        return env(key: 'EXCEPTION_RESPONSE_FORMAT', default: self::RENDER_FORMAT_IGNITION);
    }

    /**
     * Returns a structured JSON error response suitable for API clients.
     *
     * @param Throwable $throwable Exception instance to report.
     *
     * @return ResponseInterface Structured JSON error response.
     * @throws \JsonException
     * @throws \JsonException
     */
    private function renderJsonResponse(Throwable $throwable) : ResponseInterface
    {
        return $this->responseFactory->response(
            data  : json_encode(value: [
                                    'error'   => 'Internal Server Error',
                                    'message' => 'Lele! An unexpected error occurred.',
                                ],
                                flags: JSON_THROW_ON_ERROR),
            status: 500
        );
    }

    /**
     * Renders exception details using Spatie Ignition for debugging purposes.
     *
     * @param Throwable $throwable The exception instance to render.
     *
     * @return ResponseInterface Response containing Ignition detailed exception view.
     */
    private function renderIgnitionResponse(Throwable $throwable) : ResponseInterface
    {
        Ignition::make()
            ->shouldDisplayException(shouldDisplayException: true)
            ->setTheme(theme: 'dark')
            ->register();

        return $this->responseFactory->response(
            data  : Ignition::make()->renderException(throwable: $throwable),
            status: 500
        );
    }
}
