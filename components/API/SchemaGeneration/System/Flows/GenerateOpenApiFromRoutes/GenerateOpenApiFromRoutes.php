<?php

declare(strict_types=1);

namespace Avax\Components\API\SchemaGeneration\System\Flows\GenerateOpenApiFromRoutes;

use Avax\Components\HTTP\Router\System\Capabilities\RouteCollection\RouteCollection;
use Avax\Components\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\Components\HTTP\Router\System\PublicSurface\Router;

/**
 * GenerateOpenApiFromRoutes — produces a basic OpenAPI 3.0 spec from registered routes.
 *
 * Honest limitations:
 * - Routes only store method, URI, and action (no request/response schemas).
 * - Path parameters are inferred from {param} patterns in URIs.
 * - No query/header/cookie parameters are generated.
 * - No request/response content schemas are attached.
 *
 * For full OpenAPI specs, annotate routes with request/response DataObject classes
 * and use SchemaGeneration::request()/response() to attach schemas.
 */
final readonly class GenerateOpenApiFromRoutes
{
    public function __construct(
        private string $title = 'AvaX API',
        private string $version = '1.0.0',
        private string $description = 'Auto-generated OpenAPI spec from AvaX routes.',
    ) {}

    /**
     * Generate an OpenAPI 3.0 document from a Router instance.
     *
     * @return array<string, mixed>
     */
    public function fromRouter(Router $router) : array
    {
        return $this->buildSpec($router);
    }

    /**
     * Generate an OpenAPI 3.0 document from a RouteCollection.
     *
     * @return array<string, mixed>
     */
    public function fromCollection(RouteCollection $collection) : array
    {
        return $this->buildSpecFromRoutes($collection->all());
    }

    /**
     * @return array<string, mixed>
     */
    private function buildSpec(Router $router) : array
    {
        // Extract routes via reflection since Router doesn't expose collection publicly.
        // @phpstan-ignore-next-line
        $reflection = new \ReflectionClass($router);
        $prop = $reflection->getProperty('routeCollection');
        $collection = $prop->getValue($router);

        if (! $collection instanceof RouteCollection) {
            return $this->emptySpec();
        }

        return $this->buildSpecFromRoutes($collection->all());
    }

    /**
     * @param list<RouteDefinition> $routes
     * @return array<string, mixed>
     */
    private function buildSpecFromRoutes(array $routes) : array
    {
        $spec = $this->emptySpec();

        foreach ($routes as $route) {
            $method = strtolower($route->method()->value);
            $uri = $route->uri();

            // Convert {param} to OpenAPI {param} path style
            $path = $uri;
            $parameters = $this->extractPathParameters($uri);

            $spec['paths'][$path][$method] = [
                'summary' => $this->generateSummary($method, $path),
                'operationId' => $this->generateOperationId($method, $path),
                'parameters' => $parameters,
                'responses' => [
                    '200' => ['description' => 'Successful response'],
                ],
            ];
        }

        return $spec;
    }

    /**
     * @return array<string, mixed>
     */
    private function emptySpec() : array
    {
        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => $this->title,
                'version' => $this->version,
                'description' => $this->description,
            ],
            'paths' => [],
        ];
    }

    /**
     * Extract path parameters from URI pattern like /users/{id}/posts/{postId}.
     *
     * @return list<array<string, mixed>>
     */
    private function extractPathParameters(string $uri) : array
    {
        $parameters = [];

        if (preg_match_all('/\{(\w+)\}/', $uri, $matches)) {
            foreach ($matches[1] as $name) {
                $parameters[] = [
                    'name' => $name,
                    'in' => 'path',
                    'required' => true,
                    'schema' => ['type' => 'string'],
                ];
            }
        }

        return $parameters;
    }

    private function generateSummary(string $method, string $path) : string
    {
        $methodSummaries = [
            'get' => 'Retrieve',
            'post' => 'Create',
            'put' => 'Update',
            'patch' => 'Partially update',
            'delete' => 'Delete',
            'options' => 'Get options',
            'head' => 'Head request',
        ];

        $summary = $methodSummaries[$method] ?? ucfirst($method);
        $resource = $this->extractResource($path);

        return "{$summary} {$resource}";
    }

    private function generateOperationId(string $method, string $path) : string
    {
        $resource = $this->extractResource($path);
        $resource = str_replace(['/', '{', '}', '-'], '_', $resource);
        $resource = trim($resource, '_');

        return "{$method}_{$resource}";
    }

    private function extractResource(string $path) : string
    {
        // Remove path params and extract resource name
        $clean = preg_replace('/\{\w+\}/', '', $path) ?? $path;
        $segments = array_filter(explode('/', $clean));
        $last = end($segments);

        return is_string($last) ? $last : 'root';
    }
}
