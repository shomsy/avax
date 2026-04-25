<?php

declare(strict_types=1);

namespace Avax\HTTP\URI;

use Avax\HTTP\URI\Parts\Authority;
use Avax\HTTP\URI\Parts\Fragment;
use Avax\HTTP\URI\Parts\Host;
use Avax\HTTP\URI\Parts\Path;
use Avax\HTTP\URI\Parts\Port;
use Avax\HTTP\URI\Parts\Query;
use Avax\HTTP\URI\Parts\Scheme;
use Avax\HTTP\URI\Parts\UserInfo;
use InvalidArgumentException;

/**
 * Parses a URI string into parts.
 */
final class ParseUriString
{
    public static function parse(string $uri) : array
    {
        $parts = parse_url($uri);
        if ($parts === false) {
            throw new InvalidArgumentException(message: 'Invalid URI: ' . $uri);
        }

        $scheme = isset($parts['scheme']) ? new Scheme(scheme: $parts['scheme']) : null;
        $authority = null;
        if (isset($parts['host'])) {
            $userInfo = null;
            if (isset($parts['user'])) {
                $userInfo = new UserInfo(user: $parts['user'], password: $parts['pass'] ?? null);
            }
            $host      = new Host(host: $parts['host']);
            $port      = isset($parts['port']) ? new Port(port: $parts['port'], scheme: $scheme ?? new Scheme(scheme: 'http')) : null;
            $authority = new Authority(host: $host, port: $port, userInfo: $userInfo);
        }
        $path     = new Path(path: $parts['path'] ?? '/');
        $query    = new Query(queryString: $parts['query'] ?? '');
        $fragment = isset($parts['fragment']) ? new Fragment(fragment: $parts['fragment']) : null;

        return [
            'scheme'    => $scheme,
            'authority' => $authority,
            'path'      => $path,
            'query'     => $query,
            'fragment'  => $fragment,
        ];
    }
}