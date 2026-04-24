<?php

declare(strict_types=1);

namespace Avax\HTTP\URI;

use Avax\HTTP\URI\Parts\Authority;
use Avax\HTTP\URI\Parts\Fragment;
use Avax\HTTP\URI\Parts\Path;
use Avax\HTTP\URI\Parts\Query;
use Avax\HTTP\URI\Parts\Scheme;

/**
 * Parses a URI string into parts.
 */
final class ParseUriString
{
    public static function parse(string $uri) : array
    {
        $parts = parse_url($uri);
        if ($parts === false) {
            throw new \InvalidArgumentException('Invalid URI: ' . $uri);
        }

        $scheme    = isset($parts['scheme']) ? new Scheme($parts['scheme']) : null;
        $authority = null;
        if (isset($parts['host'])) {
            $userInfo = null;
            if (isset($parts['user'])) {
                $userInfo = new \Avax\HTTP\URI\Parts\UserInfo($parts['user'], $parts['pass'] ?? null);
            }
            $host      = new \Avax\HTTP\URI\Parts\Host($parts['host']);
            $port      = isset($parts['port']) ? new \Avax\HTTP\URI\Parts\Port($parts['port'], $scheme ?? new Scheme('http')) : null;
            $authority = new Authority($host, $port, $userInfo);
        }
        $path     = new Path($parts['path'] ?? '/');
        $query    = new Query($parts['query'] ?? '');
        $fragment = isset($parts['fragment']) ? new Fragment($parts['fragment']) : null;

        return [
            'scheme'    => $scheme,
            'authority' => $authority,
            'path'      => $path,
            'query'     => $query,
            'fragment'  => $fragment,
        ];
    }
}