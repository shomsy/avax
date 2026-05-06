<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Capabilities\Responses;

use Avax\Components\HTTP\Client\System\Foundation\Failure\InvalidHttpResponse;
use LibXMLError;
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
     * @param  ClientResponse  $clientResponse  The response to decode
     * @param  string|null  $format  Force a specific format ('json', 'xml', 'text')
     *
     * @throws InvalidHttpResponse if decoding fails
     */
    public function decode(ClientResponse $clientResponse, ?string $format = null): mixed
    {
        $format ??= $this->detectFormat($clientResponse);

        return match ($format) {
            'json' => $this->decodeJson($clientResponse),
            'xml' => $this->decodeXml($clientResponse),
            'text' => $clientResponse->body,
            default => throw new InvalidHttpResponse(
                message   : 'Unsupported response format: '.$format,
                statusCode: $clientResponse->statusCode,
                body      : $clientResponse->body,
            ),
        };
    }

    /**
     * Detect the response format from the Content-Type header.
     */
    private function detectFormat(ClientResponse $clientResponse): string
    {
        $contentType = $clientResponse->getContentType() ?? '';
        $contentType = strtolower($contentType);

        return match (true) {
            str_contains($contentType, 'json') => 'json',
            str_contains($contentType, 'xml') => 'xml',
            str_contains($contentType, 'text') => 'text',
            str_contains($contentType, 'html') => 'text',
            default => 'text',
        };
    }

    /**
     * Decode JSON response body.
     *
     * @param  ClientResponse  $clientResponse  The response to decode
     * @param  bool  $assoc  When true, return associative array
     *
     * @throws InvalidHttpResponse if JSON is invalid
     */
    public function decodeJson(ClientResponse $clientResponse, bool $assoc = true): mixed
    {
        if ($clientResponse->body === '') {
            throw new InvalidHttpResponse(
                message   : 'Empty response body cannot be decoded as JSON',
                statusCode: $clientResponse->statusCode,
                body      : $clientResponse->body,
            );
        }

        try {
            return json_decode($clientResponse->body, $assoc, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable $throwable) {
            throw new InvalidHttpResponse(
                message   : 'Failed to decode JSON response: '.$throwable->getMessage(),
                statusCode: $clientResponse->statusCode,
                body      : $clientResponse->body,
                previous  : $throwable,
            );
        }
    }

    /**
     * Decode XML response body.
     *
     * @param  ClientResponse  $clientResponse  The response to decode
     *
     * @throws InvalidHttpResponse if XML is invalid
     */
    public function decodeXml(ClientResponse $clientResponse): SimpleXMLElement|false
    {
        if ($clientResponse->body === '') {
            throw new InvalidHttpResponse(
                message   : 'Empty response body cannot be decoded as XML',
                statusCode: $clientResponse->statusCode,
                body      : $clientResponse->body,
            );
        }

        $previousError = libxml_use_internal_errors(true);

        try {
            $xml = simplexml_load_string($clientResponse->body);
            if ($xml === false) {
                $errors = array_map(
                    static fn (LibXMLError $libXMLError): string => $libXMLError->message,
                    libxml_get_errors(),
                );

                throw new InvalidHttpResponse(
                    message   : 'Failed to decode XML response: '.implode('; ', $errors),
                    statusCode: $clientResponse->statusCode,
                    body      : $clientResponse->body,
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
    public function decodeText(ClientResponse $clientResponse): string
    {
        return $clientResponse->body;
    }
}
