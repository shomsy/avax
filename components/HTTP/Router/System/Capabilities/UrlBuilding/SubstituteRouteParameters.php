<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Router\System\Capabilities\UrlBuilding;

use Avax\Components\HTTP\Router\System\Foundation\Failure\RouterFailure;

/** @phpstan-type RouteParameters array<string, mixed> */
final readonly class SubstituteRouteParameters
{
    public function __construct(
        private string $baseUri = 'http://localhost',
    ) {}

    /**
     * @param RouteParameters $parameters
     */
    public function substitute(string $uri, array $parameters, string $name) : string
    {
        $url = (string) preg_replace_callback(
            '/\{(\w+)\}/',
            static function (array $matches) use ($parameters, $name) : string {
                $param = $matches[1];
                if (! array_key_exists($param, $parameters)) {
                    throw new RouterFailure(
                        sprintf("Missing required parameter '%s' for route '%s'", $param, $name),
                    );
                }

                return (string) $parameters[$param];
            },
            $uri,
        );

        $remaining = [];
        foreach ($parameters as $key => $value) {
            if (! preg_match('/\{' . $key . '\}/', $uri)) {
                $remaining[$key] = $value;
            }
        }

        if ($remaining !== []) {
            $query = http_build_query($remaining);
            if ($query !== '') {
                $url .= '?' . $query;
            }
        }

        return $url;
    }

    public function makeAbsolute(string $url) : string
    {
        return $this->baseUri . ($url[0] !== '/' ? '/' : '') . $url;
    }
}
