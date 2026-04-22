<?php

declare(strict_types=1);

namespace Avax\HTTP\HttpClient\Config\Middleware;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

final class RetryMiddleware
{
    private array                    $retryStatusCodes = [504, 500, 502, 503, 429];
    private int|null                 $maxRetries       = null;
    private readonly LoggerInterface $logger;

    public function __construct(
        LoggerInterface $logger,
        int|null        $maxRetries = null,
    )
    {
        $this->logger     = $logger;
        $this->maxRetries = $maxRetries;
        $this->maxRetries ??= 3;
    }

    public function createRetryMiddleware() : callable
    {
        return Middleware::retry(
            decider: function ($retries, $request, $response = null, $exception = null) {
                $statusCode = $response?->getStatusCode();

                if ($retries >= $this->maxRetries) {
                    $this->logger->warning(message: '🔄 Retry Middleware max reached out! 🚨');

                    return false;
                }

                if ($response instanceof ResponseInterface
                    && in_array(needle: $statusCode, haystack: $this->retryStatusCodes, strict: true)) {
                    $this->logger->info(message: '🔄 Retrying due to response status: ' . $statusCode);

                    return true;
                }

                if ($exception instanceof RequestException || $exception instanceof ConnectException) {
                    $this->logger->warning(message: '🔄 Retrying due to exception: ' . $exception->getMessage());

                    return true;
                }

                return false;
            },
            delay  : static function ($retries) {
                return 1000 * (2 ** $retries); // ⏳ Exponential backoff
            }
        );
    }
}
