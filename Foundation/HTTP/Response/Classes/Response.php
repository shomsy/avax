<?php

declare(strict_types=1);

namespace Avax\HTTP\Response\Classes;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use RuntimeException;
use SimpleXMLElement;

/**
 * This class represents an HTTP response, implementing the PSR-7 ResponseInterface.
 * It can handle standard HTTP statuses, protocol versions, and content types.
 */
class Response implements ResponseInterface
{
    /**
     * Default status code - OK
     */
    private const int DEFAULT_STATUS_CODE = 200;

    /**
     * @constant {str} DEFAULT_PROTOCOL_VERSION
     * @default '1.1'
     *
     * The default protocol version used throughout the application. This is based on the assumption that most clients
     * and servers support HTTP 1.1, which balances modern usage and legacy compatibility.
     */
    private const string DEFAULT_PROTOCOL_VERSION = '1.1';

    /**
     * Commonly used HTTP status phrases.
     */
    private const array STATUS_PHRASES = [
        200 => 'OK',
        201 => 'Created',
        204 => 'No Content',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error',
        503 => 'Service Unavailable',
    ];

    /**
     * Class responsible for handling HTTP requests and responses.
     * This class encapsulates the details of creating, sending, and processing HTTP responses, ensuring that all
     * necessary details are managed consistently.
     */
    private int $statusCode;

    /**
     * Returns the reason phrase for the given HTTP status code.
     * This provides a human-readable explanation or description of the status code.
     */
    private string|null $reasonPhrase;

    /**
     * The protocol version used in HTTP communication.
     *
     * Example values could be 'HTTP/1.1' or 'HTTP/2', providing the required specificity
     * for network operations that depend on protocol differences.
     *
     * Notice: Changing this value might affect compatibility with certain servers
     * or clients, depending on the protocol compliance requirements.
     */
    private string $protocolVersion;

    /**
     * Headers to be sent with the HTTP response.
     *
     * These headers may include content-type, caching policies, or any custom
     * headers required by the business logic or standards compliance.
     * Always ensure headers are set before output is sent to the client to avoid
     * any runtime errors or unexpected behavior.
     */
    private array $headers;

    /**
     * Initializes the response with the given stream, protocol version, status code, headers, and reason phrase.
     * Reason phrase defaults to standard phrases based on the status code.
     */
    public function __construct(
        /**
         * Stream class responsible for handling and manipulating data streams.
         * This class provides methods to read and write streams, as well as manage stream state and contents.
         */
        private StreamInterface $stream,
        string|null             $protocolVersion = null,
        int|null                $statusCode = null,
        array|null              $headers = null,
        string                  $reasonPhrase = '',
    ) {
        // Default values are provided when specific values are not given.
        $this->statusCode      = $statusCode ?? self::DEFAULT_STATUS_CODE;
        $this->protocolVersion = $protocolVersion ?? self::DEFAULT_PROTOCOL_VERSION;
        $this->headers         = $this->normalizeHeaders(headers: $headers ?? []);
        $this->reasonPhrase    = $reasonPhrase !== ''
            ? $reasonPhrase
            : $this->getDefaultReasonPhrase(statusCode: $this->statusCode);
    }

    /**
     * Converts header names to lowercase and ensures all header values are arrays.
     * This normalization helps in case-insensitive lookups and consistent internal data handling.
     *
     * @param array $headers An associative array of headers where the key is the header name and the value is the
     *                       header value.
     *
     * @return array An associative array with header names in lowercase and values as arrays.
     */
    private function normalizeHeaders(array $headers) : array
    {
        $normalized = [];
        foreach ($headers as $name => $value) {
            $normalized[strtolower($name)] = (array) $value;
        }

        return $normalized;
    }

    /**
     * Retrieves the default reason phrase for a given status code.
     *
     * @param int $statusCode HTTP status code for which the reason phrase is required.
     *
     * @return string The default reason phrase corresponding to the provided status code.
     *
     * The method looks up the status code in a constant array of status phrases.
     * If the status code is not found, an empty string is returned.
     * This design ensures that even an unknown or unsupported status code won't cause an exception or error.
     */
    private function getDefaultReasonPhrase(int $statusCode) : string
    {
        return self::STATUS_PHRASES[$statusCode] ?? '';
    }

    /**
     * Retrieves the status code.
     *
     * @return int The HTTP status code.
     *
     * This method is part of a broader class responsible for handling HTTP responses.
     * The status code is crucial for determining the outcome of client-server interactions.
     *
     * Note: Ensure the $statusCode property adheres to correct HTTP status code standards (e.g., 200 for OK, 404 for
     * Not Found).
     */
    public function getStatusCode() : int
    {
        return $this->statusCode;
    }

    /**
     * Returns a new instance with the specified status code and reason phrase.
     *
     * @param int    $code         The HTTP status code. Must be between 100 and 599 inclusive.
     * @param string $reasonPhrase An optional reason phrase. If not provided, a default reason phrase will be used.
     *
     * @return ResponseInterface A new response instance with the specified status code and reason phrase.
     *
     * @throws InvalidArgumentException if the status code is not between 100 and 599.
     */
    public function withStatus(int $code, string $reasonPhrase = '') : ResponseInterface
    {
//        if ($code < 100 || $code >= 600) {
//            throw new InvalidArgumentException(message: 'Invalid status code.');
//        }

        $new               = clone $this;
        $new->statusCode   = $code;
        $new->reasonPhrase = $reasonPhrase !== ''
            ? $reasonPhrase
            : $this->getDefaultReasonPhrase(
                statusCode: $code,
            );

        return $new;
    }

    /**
     * Retrieves the reason phrase associated with the response status code.
     *
     * @return string The reason phrase, which offers a short textual description of the status code.
     */
    public function getReasonPhrase() : string
    {
        return $this->reasonPhrase;
    }

    /**
     * Retrieves the protocol version used by this instance.
     *
     * @return string The protocol version as a string.
     */
    public function getProtocolVersion() : string
    {
        return $this->protocolVersion;
    }

    /**
     * Returns a new instance with the specified HTTP protocol version.
     *
     * @param string $version The HTTP protocol version to set, e.g., '1.1', '2.0'.
     *                        Ensure the version is valid and supported by your application.
     *
     * @return ResponseInterface A new instance with the updated protocol version.
     */
    public function withProtocolVersion(string $version) : ResponseInterface
    {
        $new                  = clone $this;
        $new->protocolVersion = $version;

        return $new;
    }

    /**
     * Retrieves the headers associated with the current request or response.
     *
     * @return array An associative array of headers.
     *
     * While this method is straightforward, it is part of the broader design pattern
     * where headers are managed as an associative array. This allows for a flexible
     * and extensible way to handle HTTP headers, adhering to common practices in
     * HTTP request/response handling.
     */
    public function getHeaders() : array
    {
        return $this->headers;
    }

    /**
     * Check if the specified header exists in the request.
     *
     * @param string $name         The name of the header to check.
     *                             The method converts the header name to lowercase to ensure case-insensitive matching.
     *                             HTTP header names are case-insensitive by specification (RFC 2616), so it's important
     *                             to normalize the case to maintain consistency.
     *
     * @return bool Returns true if the header exists, false otherwise.
     */
    public function hasHeader(string $name) : bool
    {
        return array_key_exists(strtolower($name), $this->headers);
    }

    /**
     * Retrieves a single header line by name.
     *
     * This method concatenates multiple header values into a single comma-separated string.
     * This is particularly useful for headers that can have multiple values, like 'Set-Cookie' or 'Content-Type'.
     *
     * @param string $name The name of the header to retrieve.
     *
     * @return string The header values concatenated into a single string.
     */
    public function getHeaderLine(string $name) : string
    {
        return implode(',', $this->getHeader(name: $name));
    }

    /**
     * Retrieves the specified header from the headers array.
     *
     * @param string $name The name of the header to retrieve.
     *
     * @return array The header values associated with the specified name.
     *               Returns an empty array if the header is not set.
     *
     * The method ensures case-insensitive retrieval by converting the header
     * name to lowercase. This approach harmonizes with common HTTP standards
     * where header names are case-insensitive.
     */
    public function getHeader(string $name) : array
    {
        return $this->headers[strtolower($name)] ?? [];
    }

    /**
     * Returns a new instance with an added header, preserving the existing ones.
     */
    public function withAddedHeader(string $name, mixed $value) : ResponseInterface
    {
        $new                       = clone $this;
        $normalized                = strtolower($name);
        $new->headers[$normalized] = array_merge($this->headers[$normalized] ?? [], (array) $value);

        return $new;
    }

    /**
     * Returns a new instance with the specified headers added.
     *
     * This method is designed to ensure immutability by cloning the current
     * instance and applying the headers to the new instance.
     *
     * @param array $headers An associative array of headers where the key is the
     *                       header name and the value is the header value.
     *
     * @return self A new instance with the specified headers.
     */
    public function withHeaders(array $headers) : self
    {
        $new = clone $this;
        foreach ($headers as $name => $value) {
            $new = $new->withHeader(name: $name, value: $value);
        }

        return $new;
    }

    /**
     * Returns a new instance with the specified header, replacing the existing one if present.
     */
    public function withHeader(string $name, mixed $value) : ResponseInterface
    {
        $new                             = clone $this;
        $new->headers[strtolower($name)] = (array) $value;

        return $new;
    }

    /**
     * Returns a new instance without the specified header.
     */
    public function withoutHeader(string $name) : ResponseInterface
    {
        $new = clone $this;
        unset($new->headers[strtolower($name)]);

        return $new;
    }

    public function getBody() : StreamInterface
    {
        return $this->stream;
    }

    /**
     * Returns a new instance with the specified body stream.
     */
    public function withBody(StreamInterface $stream) : ResponseInterface
    {
        $new         = clone $this;
        $new->stream = $stream;

        return $new;
    }

    /**
     * Returns a new instance with JSON-encoded body data.
     *
     * @throws RuntimeException if JSON encoding fails.
     */
    public function withJson(array $data) : ResponseInterface
    {
        $new  = clone $this;
        $json = json_encode($data);
        if ($json === false) {
            throw new RuntimeException(message: 'Failed to encode JSON data: ' . json_last_error_msg());
        }

        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $json);
        fseek($stream, 0);
        $new->stream                  = new \GuzzleHttp\Psr7\Stream(stream: $stream);
        $new->headers['content-type'] = ['application/json'];

        return $new;
    }

    /**
     * Returns a new instance with XML-encoded body data.
     * This method uses SimpleXMLElement to convert an array to XML.
     */
    public function withXml(array $data) : ResponseInterface
    {
        $new = clone $this;
        $xml = new SimpleXMLElement(data: '<response/>');
        array_walk_recursive($data, function ($value, $key) use ($xml) : void {
            $xml->addChild(qualifiedName: $key, value: (string) $value);
        });
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $xml->asXML());
        fseek($stream, 0);
        $new->stream                  = new Stream(stream: $stream);
        $new->headers['content-type'] = ['application/xml'];

        return $new;
    }

    /**
     * Sends the HTTP response to the client.
     *
     * This method sets the HTTP status code and headers (if they have not
     * already been sent) and then outputs the response body to the client.
     *
     * @return $this
     */
    public function send() : self
    {
        // Set the HTTP status code and headers if they haven't been sent already
        if (! headers_sent()) {
            http_response_code($this->statusCode);

            foreach ($this->headers as $name => $values) {
                foreach ($values as $value) {
                    // Appends each header line, handling multiple values
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }

        // Output the response body to the client
        echo $this->stream->getContents();

        return $this;
    }
}
