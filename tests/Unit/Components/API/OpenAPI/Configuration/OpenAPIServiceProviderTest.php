<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\API\OpenAPI\Configuration;

use Avax\Components\API\OpenAPI\System\Capabilities\ErrorResponseSchemaGeneration\BuildErrorResponseSchema;
use Avax\Components\API\OpenAPI\System\Capabilities\PayloadSchemaGeneration\BuildPayloadSchema;
use Avax\Components\API\OpenAPI\System\Capabilities\RenderOpenApiJson\RenderOpenApiJson;
use Avax\Components\API\OpenAPI\System\Capabilities\RenderOpenApiYaml\RenderOpenApiYaml;
use Avax\Components\API\OpenAPI\System\Capabilities\SchemaGeneration\BuildOpenApiDocument;
use Avax\Components\API\OpenAPI\System\Configuration\OpenApiConfiguration;
use Avax\Components\API\OpenAPI\System\Configuration\OpenAPIServiceProvider;
use Avax\Components\API\OpenAPI\System\Flows\CompareOpenApiDocuments\CompareOpenApiDocuments;
use Avax\Components\API\OpenAPI\System\Flows\ExportOpenApiDocument\ExportOpenApiDocument;
use Avax\Components\API\OpenAPI\System\Flows\ValidateOpenApiDocument\ValidateOpenApiDocument;
use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use PHPUnit\Framework\TestCase;

final class OpenAPIServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private OpenAPIServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new OpenAPIServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_build_payload_schema_resolves(): void
    {
        $capability = $this->container->get(BuildPayloadSchema::class);

        $this->assertInstanceOf(BuildPayloadSchema::class, $capability);
    }

    public function test_build_error_response_schema_resolves(): void
    {
        $capability = $this->container->get(BuildErrorResponseSchema::class);

        $this->assertInstanceOf(BuildErrorResponseSchema::class, $capability);
    }

    public function test_openapi_configuration_resolves(): void
    {
        $config = $this->container->get(OpenApiConfiguration::class);

        $this->assertInstanceOf(OpenApiConfiguration::class, $config);
        $this->assertSame('AvaX API', $config->title);
        $this->assertSame('1.0.0', $config->version);
        $this->assertSame('/', $config->baseUrl);
    }

    public function test_build_openapi_document_resolves(): void
    {
        $capability = $this->container->get(BuildOpenApiDocument::class);

        $this->assertInstanceOf(BuildOpenApiDocument::class, $capability);
    }

    public function test_export_openapi_document_resolves(): void
    {
        $flow = $this->container->get(ExportOpenApiDocument::class);

        $this->assertInstanceOf(ExportOpenApiDocument::class, $flow);
    }

    public function test_validate_openapi_document_resolves(): void
    {
        $flow = $this->container->get(ValidateOpenApiDocument::class);

        $this->assertInstanceOf(ValidateOpenApiDocument::class, $flow);
    }

    public function test_compare_openapi_documents_resolves(): void
    {
        $flow = $this->container->get(CompareOpenApiDocuments::class);

        $this->assertInstanceOf(CompareOpenApiDocuments::class, $flow);
    }

    public function test_render_openapi_json_resolves(): void
    {
        $capability = $this->container->get(RenderOpenApiJson::class);

        $this->assertInstanceOf(RenderOpenApiJson::class, $capability);
    }

    public function test_render_openapi_yaml_resolves(): void
    {
        $capability = $this->container->get(RenderOpenApiYaml::class);

        $this->assertInstanceOf(RenderOpenApiYaml::class, $capability);
    }
}
