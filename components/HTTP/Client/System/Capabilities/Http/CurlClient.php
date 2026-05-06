<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Capabilities\Http;

use RuntimeException;

final class CurlClient
{
    public function send(ClientRequest $clientRequest): ClientResponse
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $clientRequest->url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($clientRequest->method));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $headers = [];
        foreach ($clientRequest->headers as $name => $value) {
            $headers[] = sprintf('%s: %s', $name, $value);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($clientRequest->body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $clientRequest->body);
        }

        // Options
        if (isset($clientRequest->options['timeout'])) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $clientRequest->options['timeout']);
        }

        $response = curl_exec($ch);

        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);

            throw new RuntimeException('HTTP Request failed: '.$error);
        }

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $headerContent = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        $respHeaders = [];
        foreach (explode("\r\n", $headerContent) as $line) {
            if (str_contains($line, ':')) {
                [$name, $value] = explode(':', $line, 2);
                $respHeaders[trim($name)] = trim($value);
            }
        }

        curl_close($ch);

        return new ClientResponse($statusCode, $respHeaders, $body);
    }
}
