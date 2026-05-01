<?php

declare(strict_types=1);

namespace Avax\Components\Documentation\Api\System\Capabilities\OpenApi;

final readonly class OpenApiGenerator
{
    public function __construct(
        private string $title = 'Avax API',
        private string $version = '1.0.0',
    ) {}

    /**
     * @param  list<array{method:string,path:string,summary?:string,tags?:list<string>}>  $routes
     */
    public function generate(array $routes): array
    {
        $paths = [];

        foreach ($routes as $route) {
            $method = strtolower(string: $route['method']);
            $paths[$route['path']][$method] = [
                'summary' => $route['summary'] ?? $method.' '.$route['path'],
                'tags' => $route['tags'] ?? ['Application'],
                'responses' => [
                    '200' => [
                        'description' => 'Successful response',
                    ],
                ],
            ];
        }

        ksort(array: $paths);

        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => $this->title,
                'version' => $this->version,
            ],
            'paths' => $paths,
        ];
    }
}
