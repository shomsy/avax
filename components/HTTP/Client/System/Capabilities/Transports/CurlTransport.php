<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Capabilities\Transports;

use Avax\Components\HTTP\Client\System\Capabilities\Requests\OutboundRequest;
use Avax\Components\HTTP\Client\System\Capabilities\Requests\RequestOptions;
use Avax\Components\HTTP\Client\System\Capabilities\Responses\ClientResponse;
use Avax\Components\HTTP\Client\System\Foundation\Failure\HttpRequestFailed;
use Avax\Components\HTTP\Client\System\Foundation\Failure\HttpTimeout;
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
     * @param OutboundRequest $request The outbound request to send
     *
     * @return ClientResponse The HTTP response
     *
     * @throws HttpRequestFailed if the request cannot be completed
     * @throws HttpTimeout if the request times out
     */
    public function send(OutboundRequest $request) : ClientResponse
    {
        $options = $request->options ?? new RequestOptions();

        $ch = curl_init();
        if ($ch === false) {
            throw new HttpRequestFailed('Failed to initialize cURL handle');
        }

        try {
            $this->configureCurl($ch, $request, $options);

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
                $this->handleCurlError($ch, $curlErrno, $curlError, $request);
            }

            // Parse the response
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headerStr  = substr($rawResponse, 0, $headerSize);
            $body       = substr($rawResponse, $headerSize);

            $statusCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $effectiveUrl  = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) ?: null;
            $redirectCount = curl_getinfo($ch, CURLINFO_REDIRECT_COUNT) ?: 0;

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
        CurlHandle     $ch,
        OutboundRequest $request,
        RequestOptions  $options,
    ) : void
    {
        // Basic options
        curl_setopt_array($ch, [
            CURLOPT_URL               => $request->url,
            CURLOPT_RETURNTRANSFER    => true,
            CURLOPT_HEADER            => true,
            CURLOPT_FOLLOWLOCATION    => $options->followRedirects,
            CURLOPT_MAXREDIRS         => $options->maxRedirects,
            CURLOPT_CONNECTTIMEOUT_MS => $options->connectTimeout,
            CURLOPT_TIMEOUT_MS        => $options->timeout,
            CURLOPT_SSL_VERIFYPEER    => $options->verifySsl,
            CURLOPT_SSL_VERIFYHOST    => $options->verifySsl ? 2 : 0,
            CURLOPT_CUSTOMREQUEST     => $request->method,
        ]);

        // Set headers
        $headers = $this->buildHeaderArray($request);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        // Set body for methods that support it
        if ($request->body !== null && ! in_array(strtoupper($request->method), ['GET', 'HEAD'], true)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $this->normalizeBody($request->body));
        }

        // SSL certificate options
        if ($options->sslCertPath !== null) {
            curl_setopt($ch, CURLOPT_SSLCERT, $options->sslCertPath);
        }
        if ($options->sslKeyPath !== null) {
            curl_setopt($ch, CURLOPT_SSLKEY, $options->sslKeyPath);
        }

        // Proxy options
        if ($options->proxy !== null) {
            curl_setopt($ch, CURLOPT_PROXY, $options->proxy);
            if ($options->proxyAuth !== null) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $options->proxyAuth);
            }
        }

        // Additional curl options
        foreach ($options->additional as $key => $value) {
            if (is_int($key)) {
                curl_setopt($ch, $key, $value);
            }
        }
    }

    /**
     * Build the header array for cURL.
     *
     * @return list<string>
     */
    private function buildHeaderArray(OutboundRequest $request) : array
    {
        $headers = [];
        foreach ($request->headers as $name => $value) {
            $headers[] = "{$name}: {$value}";
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
        CurlHandle     $ch,
        int             $errno,
        string          $error,
        OutboundRequest $request,
    ) : never
    {
        $url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) ?: $request->url;

        // Timeout errors
        if (in_array($errno, [CURLE_OPERATION_TIMEDOUT, CURLE_COULDNT_CONNECT], true)) {
            throw new HttpTimeout(
                message  : "HTTP request timed out: {$error}",
                timeoutMs: $request->options?->timeout ?? RequestOptions::DEFAULT_TIMEOUT,
                url      : $url,
                method   : $request->method,
            );
        }

        throw new HttpRequestFailed(
            message: "cURL error ({$errno}): {$error}",
            url    : $url,
            method : $request->method,
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
            if ($line === '' || str_contains($line, 'HTTP/')) {
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
            default => "Status {$statusCode}",
        };
    }

    /**
     * Build a PSR-7 request from the outbound request.
     */
    public function buildPsr7Request(OutboundRequest $request) : Request
    {
        $body = $this->normalizeBody($request->body);

        return new Request(
            $request->method,
            $request->url,
            $request->headers,
            $body,
        );
    }
}
