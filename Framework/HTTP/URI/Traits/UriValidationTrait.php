<?php

declare(strict_types=1);

namespace Gemini\HTTP\URI\Traits;

use InvalidArgumentException;

trait UriValidationTrait
{
    public function validateScheme(string $scheme) : string
    {
        $allowedSchemes = ['http', 'https', 'ftp', 'ws', 'wss', 'file', 'mailto', 'data', 'blob'];
        if ($scheme === '' || ! in_array(strtolower($scheme), $allowedSchemes, true)) {
            throw new InvalidArgumentException(message: 'Invalid scheme: ' . $scheme);
        }

        return strtolower($scheme);
    }

    public function validateHost(string $host) : string
    {
        if ($host === '') {
            return '';
        }

        if (str_starts_with($host, '[') && str_ends_with($host, ']')) {
            $ipv6 = trim($host, '[]');
            if (! filter_var($ipv6, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                throw new InvalidArgumentException(message: 'Invalid IPv6 host: ' . $host);
            }

            return $host;
        }

        $asciiHost = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
        if ($asciiHost === false || ! filter_var($asciiHost, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
            throw new InvalidArgumentException(message: 'Invalid host: ' . $host);
        }

        return strtolower($asciiHost);
    }

    public function validatePath(string $path) : string
    {
        $segments = array_map(
            static fn(string $segment) : string => rawurlencode($segment),
            explode('/', $path)
        );

        return '/' . ltrim(implode('/', $segments), '/');
    }

    public function validateQuery(string $query) : string
    {
        parse_str($query, $queryArray);
        if (! is_array($queryArray)) {
            throw new InvalidArgumentException(message: 'Invalid query string: ' . $query);
        }

        return http_build_query($queryArray, '', '&', PHP_QUERY_RFC3986);
    }

    public function validateFragment(string $fragment) : string
    {
        return rawurlencode($fragment);
    }

    public function validatePort(?int $port, string $scheme) : ?int
    {
        if ($port === null) {
            return null;
        }

        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException(message: 'Invalid port: ' . $port);
        }

        $defaultPorts = [
            'http'  => 80,
            'https' => 443,
            'ftp'   => 21,
            'ws'    => 80,
            'wss'   => 443,
        ];

        return $port === ($defaultPorts[$scheme] ?? null) ? null : $port;
    }
}
