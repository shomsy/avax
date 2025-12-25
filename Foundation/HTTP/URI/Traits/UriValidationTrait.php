<?php

declare(strict_types=1);

namespace Avax\HTTP\URI\Traits;

use InvalidArgumentException;

trait UriValidationTrait
{
    public function validateScheme(string $scheme) : string
    {
        $allowedSchemes = ['http', 'https', 'ftp', 'ws', 'wss', 'file', 'mailto', 'data', 'blob'];
        if ($scheme === '' || ! in_array(needle: strtolower(string: $scheme), haystack: $allowedSchemes, strict: true)) {
            throw new InvalidArgumentException(message: 'Invalid scheme: ' . $scheme);
        }

        return strtolower(string: $scheme);
    }

    public function validateHost(string $host) : string
    {
        if ($host === '') {
            return '';
        }

        if (str_starts_with(haystack: $host, needle: '[') && str_ends_with(haystack: $host, needle: ']')) {
            $ipv6 = trim(string: $host, characters: '[]');
            if (! filter_var(value: $ipv6, filter: FILTER_VALIDATE_IP, options: FILTER_FLAG_IPV6)) {
                throw new InvalidArgumentException(message: 'Invalid IPv6 host: ' . $host);
            }

            return $host;
        }

        $asciiHost = idn_to_ascii(domain: $host, flags: IDNA_DEFAULT, variant: INTL_IDNA_VARIANT_UTS46);
        if ($asciiHost === false || ! filter_var(value: $asciiHost, filter: FILTER_VALIDATE_DOMAIN, options: FILTER_FLAG_HOSTNAME)) {
            throw new InvalidArgumentException(message: 'Invalid host: ' . $host);
        }

        return strtolower(string: $asciiHost);
    }

    public function validatePath(string $path) : string
    {
        $segments = array_map(
            callback: static fn(string $segment) : string => rawurlencode(string: $segment),
            array   : explode(separator: '/', string: $path)
        );

        return '/' . ltrim(string: implode(separator: '/', array: $segments), characters: '/');
    }

    public function validateQuery(string $query) : string
    {
        parse_str(string: $query, result: $queryArray);
        if (! is_array(value: $queryArray)) {
            throw new InvalidArgumentException(message: 'Invalid query string: ' . $query);
        }

        return http_build_query(data: $queryArray, numeric_prefix: '', arg_separator: '&', encoding_type: PHP_QUERY_RFC3986);
    }

    public function validateFragment(string $fragment) : string
    {
        return rawurlencode(string: $fragment);
    }

    public function validatePort(int|null $port, string $scheme) : int|null
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
