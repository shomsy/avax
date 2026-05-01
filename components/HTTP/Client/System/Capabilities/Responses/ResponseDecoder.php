<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Capabilities\Responses;

use Avax\Components\HTTP\Client\System\Foundation\Failure\InvalidHttpResponse;
use SimpleXMLElement;
use Throwable;

/**
 * ResponseDecoder - Decodes HTTP response bodies into various formats.
 *
 * Supports automatic format detection based on Content-Type header
 * and manual format specification.
 */
final class ResponseDecoder
{
    /**
     * Decode a response body based on the specified or detected format.
     *
     * @param ClientResponse $response The response to decode
     * @param string|null $format Force a specific format ('json', 'xml', 'text')
     *
     * @throws InvalidHttpResponse if decoding fails
     */
    public function decode(ClientResponse $response, string $format = null) : mixed
    {
        $format ??= $this->detectFormat($response);

        return match ($format) {
            'json'  => $this->decodeJson($response),
            'xml'   => $this->decodeXml($response),
            'text'  => $response->body,
            default => throw new InvalidHttpResponse(
                message   : "Unsupported response format: {$format}",
                statusCode: $response->statusCode,
                body      : $response->body,
            ),
        };
    }

    /**
     * Detect the response format from the Content-Type header.
     */
    private function detectFormat(ClientResponse $response) : string
    {
        $contentType = $response->getContentType() ?? '';
        $contentType = strtolower($contentType);

        return match (true) {
            str_contains($contentType, 'json') => 'json',
            str_contains($contentType, 'xml')  => 'xml',
            str_contains($contentType, 'text') => 'text',
            str_contains($contentType, 'html') => 'text',
            default                            => 'text',
        };
    }

    /**
     * Decode JSON response body.
     *
     * @param ClientResponse $response The response to decode
     * @param bool $assoc When true, return associative array
     *
     * @throws InvalidHttpResponse if JSON is invalid
     */
    public function decodeJson(ClientResponse $response, bool $assoc = true) : mixed
    {
        if ($response->body === '') {
            throw new InvalidHttpResponse(
                message   : 'Empty response body cannot be decoded as JSON',
                statusCode: $response->statusCode,
                body      : $response->body,
            );
        }

        try {
            return json_decode($response->body, $assoc, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            throw new InvalidHttpResponse(
                message   : "Failed to decode JSON response: {$e->getMessage()}",
                statusCode: $response->statusCode,
                body      : $response->body,
                previous  : $e,
            );
        }
    }

    /**
     * Decode XML response body.
     *
     * @param ClientResponse $response The response to decode
     *
     * @throws InvalidHttpResponse if XML is invalid
     */
    public function decodeXml(ClientResponse $response) : SimpleXMLElement|false
    {
        if ($response->body === '') {
            throw new InvalidHttpResponse(
                message   : 'Empty response body cannot be decoded as XML',
                statusCode: $response->statusCode,
                body      : $response->body,
            );
        }

        $previousError = libxml_use_internal_errors(true);

        try {
            $xml = simplexml_load_string($response->body);
            if ($xml === false) {
                $errors = array_map(
                    static fn ($error) => $error->message,
                    libxml_get_errors(),
                );

                throw new InvalidHttpResponse(
                    message   : 'Failed to decode XML response: ' . implode('; ', $errors),
                    statusCode: $response->statusCode,
                    body      : $response->body,
                );
            }

            return $xml;
        } finally {
            libxml_use_internal_errors($previousError);
        }
    }

    /**
     * Decode as plain text (pass-through).
     */
    public function decodeText(ClientResponse $response) : string
    {
        return $response->body;
    }
}
