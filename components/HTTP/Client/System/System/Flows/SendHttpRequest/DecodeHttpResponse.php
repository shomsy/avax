<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\System\Flows\SendHttpRequest;

use Avax\Components\HTTP\Client\System\System\Capabilities\Responses\ClientResponse;

/**
 * DecodeHttpResponse - Decodes raw HTTP response data into a ClientResponse.
 *
 * Takes raw HTTP response data (status code, headers, body, timing info)
 * and constructs a properly formatted ClientResponse value object.
 */
final class DecodeHttpResponse
{
    /**
     * Decode raw HTTP response data into a ClientResponse.
     *
     * @param int                   $statusCode     HTTP status code
     * @param array<string, string> $headers        Response headers
     * @param string                $body           Response body
     * @param string                $reasonPhrase   HTTP reason phrase
     * @param string                $protocol       HTTP protocol version
     * @param float                 $transferTimeMs Transfer time in ms
     * @param float                 $connectTimeMs  Connect time in ms
     * @param float                 $totalTimeMs    Total time in ms
     * @param int                   $redirectCount  Number of redirects
     * @param string|null           $effectiveUrl   Final URL after redirects
     *
     * @return ClientResponse The decoded response
     */
    public function decode(
        int     $statusCode,
        array   $headers,
        string  $body,
        string  $reasonPhrase = '',
        string  $protocol = '1.1',
        float   $transferTimeMs = 0.0,
        float   $connectTimeMs = 0.0,
        float   $totalTimeMs = 0.0,
        int     $redirectCount = 0,
        ?string $effectiveUrl = null,
    ) : ClientResponse
    {
        // Normalize headers to array of arrays
        $normalizedHeaders = [];
        foreach ($headers as $name => $value) {
            $normalizedHeaders[$name] = is_array($value) ? $value : [$value];
        }

        return new ClientResponse(
            statusCode    : $statusCode,
            headers       : $normalizedHeaders,
            body          : $body,
            reasonPhrase  : $reasonPhrase !== '' ? $reasonPhrase : $this->defaultReasonPhrase($statusCode),
            protocol      : $protocol,
            transferTimeMs: $transferTimeMs,
            connectTimeMs : $connectTimeMs,
            totalTimeMs   : $totalTimeMs,
            redirectCount : $redirectCount,
            effectiveUrl  : $effectiveUrl,
        );
    }

    /**
     * Get a default reason phrase for a status code.
     */
    private function defaultReasonPhrase(int $statusCode) : string
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
}
