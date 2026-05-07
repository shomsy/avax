<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\System\Capabilities\Transports;

use Avax\Components\HTTP\Client\System\System\Capabilities\Requests\OutboundRequest;
use Avax\Components\HTTP\Client\System\System\Capabilities\Requests\RequestOptions;
use Avax\Components\HTTP\Client\System\System\Capabilities\Responses\ClientResponse;
use Avax\Components\HTTP\Client\System\System\Foundation\Failure\HttpRequestFailed;
use Avax\Components\HTTP\Client\System\System\Foundation\Failure\HttpTimeout;
use CurlHandle;
use GuzzleHttp\Psr7\Request;

/**
 * CurlTransport - cURL-based HTTP transport implementation.
 *
 * Uses PHP's curl_* functions to execute HTTP requests with full
 * support for SSL, proxies, timeouts, and other HTTP features.
 */
final class CurlTransport implements HttpTransportInterface
{
    /**
     * Send an HTTP request using cURL.
     *
     * @param OutboundRequest $outboundRequest The outbound request to send
     *
     * @return ClientResponse The HTTP response
     *
     * @throws HttpRequestFailed if the request cannot be completed
     * @throws HttpTimeout if the request times out
     */
    public function send(OutboundRequest $outboundRequest) : ClientResponse
    {
        $options = $outboundRequest->options ?? new RequestOptions();

        $ch = curl_init();
        if ($ch === false) {
            throw new HttpRequestFailed('Failed to initialize cURL handle');
        }

        try {
            $this->configureCurl($ch, $outboundRequest, $options);

            $startTime   = microtime(true);
            $rawResponse = curl_exec($ch);
            $endTime     = microtime(true);

            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);

            // Get timing info from cURL
            $connectTimeMs = (curl_getinfo($ch, CURLINFO_CONNECT_TIME) ?: 0.0) * 1000;
            $totalTimeMs   = (curl_getinfo($ch, CURLINFO_TOTAL_TIME) ?: 0.0) * 1000;
            if ($totalTimeMs === 0.0) {
                $totalTimeMs = ($endTime - $startTime) * 1000;
            }

            // Handle cURL errors
            if ($rawResponse === false) {
                $this->handleCurlError($ch, $curlErrno, $curlError, $outboundRequest);
            }

            // Parse the response
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headerStr  = substr($rawResponse, 0, $headerSize);
            $body       = substr($rawResponse, $headerSize);

            $statusCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $effectiveUrl  = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) ?: null;
            $redirectCount = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT);

            $headers      = $this->parseHeaders($headerStr);
            $reasonPhrase = curl_getinfo($ch, CURLINFO_HTTP_VERSION) !== false
                ? $this->getReasonPhrase($statusCode)
                : '';

            $protocol = match (curl_getinfo($ch, CURLINFO_HTTP_VERSION)) {
                CURL_HTTP_VERSION_2   => '2.0',
                CURL_HTTP_VERSION_2_0 => '2.0',
                CURL_HTTP_VERSION_1_1 => '1.1',
                CURL_HTTP_VERSION_1_0 => '1.0',
                default               => '1.1',
            };

            return new ClientResponse(
                statusCode    : $statusCode,
                headers       : $headers,
                body          : $body,
                reasonPhrase  : $reasonPhrase,
                protocol      : $protocol,
                transferTimeMs: $totalTimeMs - $connectTimeMs,
                connectTimeMs : $connectTimeMs,
                totalTimeMs   : $totalTimeMs,
                redirectCount : $redirectCount,
                effectiveUrl  : $effectiveUrl,
            );
        } finally {
            curl_close($ch);
        }
    }

    /**
     * Configure cURL options for the request.
     */
    private function configureCurl(
        CurlHandle      $curlHandle,
        OutboundRequest $outboundRequest,
        RequestOptions  $requestOptions,
    ) : void
    {
        // Basic options
        curl_setopt_array($curlHandle, [
            CURLOPT_URL               => $outboundRequest->url,
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_HEADER            => true,
            CURLOPT_FOLLOWLOCATION    => $requestOptions->followRedirects,
            CURLOPT_MAXREDIRS         => $requestOptions->maxRedirects,
            CURLOPT_CONNECTTIMEOUT_MS => $requestOptions->connectTimeout,
            CURLOPT_TIMEOUT_MS        => $requestOptions->timeout,
            CURLOPT_SSL_VERIFYPEER    => $requestOptions->verifySsl,
            CURLOPT_SSL_VERIFYHOST    => $requestOptions->verifySsl ? 2 : 0,
            CURLOPT_CUSTOMREQUEST     => $outboundRequest->method,
        ]);

        // Set headers
        $headers = $this->buildHeaderArray($outboundRequest);
        curl_setopt($curlHandle, CURLOPT_HTTPHEADER, $headers);

        // Set body for methods that support it
        if ($outboundRequest->body !== null && ! in_array(strtoupper($outboundRequest->method), ['GET', 'HEAD'], true)) {
            curl_setopt($curlHandle, CURLOPT_POSTFIELDS, $this->normalizeBody($outboundRequest->body));
        }

        // SSL certificate options
        if ($requestOptions->sslCertPath !== null) {
            curl_setopt($curlHandle, CURLOPT_SSLCERT, $requestOptions->sslCertPath);
        }

        if ($requestOptions->sslKeyPath !== null) {
            curl_setopt($curlHandle, CURLOPT_SSLKEY, $requestOptions->sslKeyPath);
        }

        // Proxy options
        if ($requestOptions->proxy !== null) {
            curl_setopt($curlHandle, CURLOPT_PROXY, $requestOptions->proxy);
            if ($requestOptions->proxyAuth !== null) {
                curl_setopt($curlHandle, CURLOPT_PROXYUSERPWD, $requestOptions->proxyAuth);
            }
        }

        // Additional curl options
        foreach ($requestOptions->additional as $key => $value) {
            if (is_int($key)) {
                curl_setopt($curlHandle, $key, $value);
            }
        }
    }

    /**
     * Build the header array for cURL.
     *
     * @return list<string>
     */
    private function buildHeaderArray(OutboundRequest $outboundRequest) : array
    {
        $headers = [];
        foreach ($outboundRequest->headers as $name => $value) {
            $headers[] = sprintf('%s: %s', $name, $value);
        }

        return $headers;
    }

    /**
     * Normalize the request body to a string.
     */
    private function normalizeBody(mixed $body) : string
    {
        if ($body === null) {
            return '';
        }

        if (is_string($body)) {
            return $body;
        }

        if (is_array($body)) {
            return json_encode($body, JSON_THROW_ON_ERROR);
        }

        return (string) $body;
    }

    /**
     * Handle cURL errors by throwing appropriate exceptions.
     *
     * @throws HttpTimeout for timeout errors
     * @throws HttpRequestFailed for other errors
     */
    private function handleCurlError(
        CurlHandle      $curlHandle,
        int             $errno,
        string          $error,
        OutboundRequest $outboundRequest,
    ) : never
    {
        $url = curl_getinfo($curlHandle, CURLINFO_EFFECTIVE_URL) ?: $outboundRequest->url;

        // Timeout errors
        if (in_array($errno, [CURLE_OPERATION_TIMEDOUT, CURLE_COULDNT_CONNECT], true)) {
            throw new HttpTimeout(
                message  : 'HTTP request timed out: ' . $error,
                timeoutMs: $outboundRequest->options?->timeout ?? RequestOptions::DEFAULT_TIMEOUT,
                url      : $url,
                method   : $outboundRequest->method,
            );
        }

        throw new HttpRequestFailed(
            message: sprintf('cURL error (%s): %s', $errno, $error),
            url    : $url,
            method : $outboundRequest->method,
            reason : $error,
        );
    }

    /**
     * Parse raw HTTP headers into an array.
     *
     * @return array<string, list<string>>
     */
    private function parseHeaders(string $headerStr) : array
    {
        $headers = [];
        $lines   = explode("\r\n", $headerStr);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if (str_contains($line, 'HTTP/')) {
                continue;
            }

            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $name             = trim($parts[0]);
                $value            = trim($parts[1]);
                $headers[$name][] = $value;
            }
        }

        return $headers;
    }

    /**
     * Get the standard reason phrase for a status code.
     */
    private function getReasonPhrase(int $statusCode) : string
    {
        return match ($statusCode) {
            200     => 'OK',
            201     => 'Created',
            204     => 'No Content',
            301     => 'Moved Permanently',
            302     => 'Found',
            304     => 'Not Modified',
            400     => 'Bad Request',
            401     => 'Unauthorized',
            403     => 'Forbidden',
            404     => 'Not Found',
            405     => 'Method Not Allowed',
            408     => 'Request Timeout',
            422     => 'Unprocessable Entity',
            429     => 'Too Many Requests',
            500     => 'Internal Server Error',
            502     => 'Bad Gateway',
            503     => 'Service Unavailable',
            504     => 'Gateway Timeout',
            default => 'Status ' . $statusCode,
        };
    }

    /**
     * Build a PSR-7 request from the outbound request.
     */
    public function buildPsr7Request(OutboundRequest $outboundRequest) : Request
    {
        $body = $this->normalizeBody($outboundRequest->body);

        return new Request(
            $outboundRequest->method,
            $outboundRequest->url,
            $outboundRequest->headers,
            $body,
        );
    }
}
