<?php

declare(strict_types=1);

namespace Avax\HTTP\Response;

use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Factory for creating standardized PSR-7 HTTP responses.
 */
readonly class ResponseFactory implements ResponseFactoryInterface
{
    public function __construct(
        private StreamFactoryInterface $streamFactory,
        private ResponseInterface      $response
    ) {}

    /**
     * Generates and returns an HTTP Response object based on the provided data and status code.
     *
     * This method delegates to the `send` method to decide the appropriate response format (JSON, plain text, etc.)
     * based on the type of `$data`. It provides a flexible mechanism to handle various types of responses, keeping the
     * controller concise and focused on defining only high-level response creation.
     *
     * @param mixed $data   The data to be sent in the response. Supports different types such as arrays, objects,
     *                      plain strings, or already-prepared `ResponseInterface` instances.
     * @param int   $status The HTTP status code to be associated with the response. Defaults to 200 (OK).
     *
     * @return ResponseInterface Returns a fully constructed HTTP response object.
     */
    public function response(mixed $data, int $status = 200) : ResponseInterface
    {
        // Delegate the task of creating a response object to the `send` method.
        // The `send` method handles different data types accordingly (e.g., JSON encoding, plain string content, etc.).
        return $this->send(data: $data, status: $status);
    }

    /**
     * Generates a response based on data type.
     */
    public function send(mixed $data, int $status = 200) : ResponseInterface
    {
        return match (true) {
            $data instanceof ResponseInterface  => $data,
            is_array($data) || is_object($data) => $this->createJsonResponse(data: (array) $data, status: $status),
            is_string($data)                    => $this->createTextResponse(content: $data, status: $status),
            default                             => $this->createResponseWithBody(
                content: (string) ($data ?? ''),
                status : $status
            ),
        };
    }

    /**
     * Creates a JSON response with proper encoding.
     */
    public function createJsonResponse(array $data, int $status = 200) : ResponseInterface
    {
        try {
            $json = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $jsonException) {
            return $this->createErrorResponse(message: "JSON encoding failed: " . $jsonException->getMessage());
        }

        $stream = $this->streamFactory->createStream(content: $json);

        return $this
            ->cloneResponse()
            ->withStatus(code: $status)
            ->withBody(stream: $stream)
            ->withHeader(name: 'Content-Type', value: 'application/json');
    }

    /**
     * Creates an error response.
     */
    private function createErrorResponse(string $message) : ResponseInterface
    {
        return $this->createJsonResponse(data: ['error' => $message], status: 500);
    }

    /**
     * Clones the base response to ensure immutability.
     */
    private function cloneResponse() : ResponseInterface
    {
        return clone $this->response;
    }

    /**
     * Creates a plain text response.
     */
    public function createTextResponse(string $content, int $status = 200) : ResponseInterface
    {
        $stream = $this->streamFactory->createStream(content: $content);

        return $this
            ->cloneResponse()
            ->withStatus(code: $status)
            ->withBody(stream: $stream)
            ->withHeader(name: 'Content-Type', value: 'text/plain');
    }

    /**
     * Creates a generic response with body content.
     */
    public function createResponseWithBody(string $content, int $status, array $headers = []) : ResponseInterface
    {
        $stream   = $this->streamFactory->createStream(content: $content);
        $response = $this
            ->cloneResponse()
            ->withStatus(code: $status)
            ->withBody(stream: $stream);

        foreach ($headers as $header => $value) {
            $response = $response->withHeader(name: $header, value: $value);
        }

        return $response;
    }

    /**
     * Creates a new empty response with a status code and reason phrase.
     */
    public function createResponse(int|null $code = null, string $reasonPhrase = '') : ResponseInterface
    {
        $code   ??= 200;
        $stream = $this->streamFactory->createStream();

        return $this
            ->cloneResponse()
            ->withStatus(code: $code, reasonPhrase: $reasonPhrase)
            ->withBody(stream: $stream);
    }

    /**
     * Creates a redirect response (supports absolute and relative URLs).
     */
    public function createRedirectResponse(string $url, int $status = 302) : ResponseInterface
    {
        if (! filter_var($url, FILTER_VALIDATE_URL) && ! str_starts_with($url, '/')) {
            throw new InvalidArgumentException(message: 'Invalid URL for redirection.');
        }

        return $this
            ->cloneResponse()
            ->withStatus(code: $status)
            ->withHeader(name: 'Location', value: $url);
    }

    /**
     * Creates an HTML response.
     */
    public function createHtmlResponse(string $html, int $status = 200) : ResponseInterface
    {
        $stream = $this->streamFactory->createStream($html);

        return $this
            ->cloneResponse()
            ->withStatus($status)
            ->withBody($stream)
            ->withHeader('Content-Type', 'text/html; charset=UTF-8');
    }

    /**
     * Renders a view template with the provided data and returns a response.
     *
     * @param string     $template The name of the template to render.
     * @param array|null $data     Optional data to pass to the template. Defaults to an empty array if null.
     * @param int        $status   The HTTP status code for the response. Defaults to 200.
     *
     * @return ResponseInterface The generated HTTP response containing the rendered view.
     */
    public function view(string $template, array|null $data = null, int $status = 200) : ResponseInterface
    {
        $data ??= [];

        return view($template, $data);
    }

}
