<?php

declare(strict_types=1);

namespace Avax\HTTP\HttpClient\Traits;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;

trait SendsHttpRequestsTrait
{
    /**
     * Core method for sending synchronous HTTP requests.
     *
     * @param string     $method  HTTP method (GET, POST, etc.).
     * @param string     $url     The URL endpoint.
     * @param array|null $headers Optional headers for the request.
     * @param array      $options Additional options for the request.
     *
     * @return ResponseInterface The raw HTTP response.
     * @throws \RuntimeException
     */
    public function sendRequest(
        string     $method,
        string     $url,
        array|null $headers = null,
        array      $options = [],
    ) : ResponseInterface {
        $headers            ??= [];
        $options['headers'] = $headers;

        try {
            return $this->httpClient->request(method: $method, uri: $url, options: $options);
        } catch (Throwable $throwable) {
            throw new RuntimeException(
                message : 'Failed to send request to ' . $url,
                code    : (int) $throwable->getCode(),
                previous: $throwable,
            );
        }
    }
}
