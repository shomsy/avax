<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\PublicSurface;

use Avax\Components\HTTP\Client\System\Capabilities\Http\ClientRequest;
use Avax\Components\HTTP\Client\System\Capabilities\Http\ClientResponse;
use Avax\Components\HTTP\Client\System\Capabilities\Http\CurlClient;

final readonly class HttpClient
{
    public function __construct(
        private CurlClient $client = new CurlClient,
    ) {}

    public function get(string $url, array $headers = [], array $options = []) : ClientResponse
    {
        return $this->send(new ClientRequest('GET', $url, $headers, null, $options));
    }

    public function post(string $url, mixed $data = null, array $headers = [], array $options = []) : ClientResponse
    {
        $body = is_array($data) ? json_encode($data) : (string) $data;
        if (is_array($data) && ! isset($headers['Content-Type'])) {
            $headers['Content-Type'] = 'application/json';
        }

        return $this->send(new ClientRequest('POST', $url, $headers, $body, $options));
    }

    public function send(ClientRequest $request) : ClientResponse
    {
        return $this->client->send($request);
    }
}
