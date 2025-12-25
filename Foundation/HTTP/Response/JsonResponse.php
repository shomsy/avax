<?php

declare(strict_types=1);

namespace Avax\HTTP\Response;

use JsonException;
use Psr\Http\Message\ResponseInterface;

/**
 * Utility for creating standardized JSON HTTP responses.
 */
readonly class JsonResponse
{
    private const array DEFAULT_HEADERS = ['Content-Type' => 'application/json'];

    public function __construct(
        public int    $status,
        public string $message = '',
        public array  $data = [],
        public array  $headers = self::DEFAULT_HEADERS,
    ) {}

    /**
     * Creates a success response.
     */
    public static function success(string|null $message = null, array $data = []) : self
    {
        return new self(
            status : 200,
            message: $message ?? 'Success',
            data   : $data
        );
    }

    /**
     * Creates an error response.
     */
    public static function error(string|null $message = null, int $status = 500) : self
    {
        return new self(
            status : $status,
            message: $message ?? 'An error occurred'
        );
    }

    /**
     * Creates a failure response (business logic failure).
     */
    public static function failure(string|null $message = null, array $data = []) : self
    {
        return new self(
            status : 400,
            message: $message ?? 'Operation failed',
            data   : $data
        );
    }

    /**
     * Converts response to a PSR-7 compatible JSON response.
     *
     * @throws JsonException
     */
    public function toResponse() : ResponseInterface
    {
        $response = app(abstract: ResponseFactory::class)->createResponse($this->status);

        foreach ($this->headers as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        $response->getBody()->write($this->toJson());

        return $response;
    }

    /**
     * Converts response data to JSON string.
     *
     * @throws JsonException
     */
    public function toJson() : string
    {
        return json_encode(value: $this->toArray(), flags: JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Converts response data to an array.
     */
    public function toArray() : array
    {
        return [
            'status'  => $this->status,
            'message' => $this->message,
            'data'    => $this->data,
        ];
    }
}
