<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\API\SchemaGeneration\Configuration;

use Avax\Components\API\SchemaGeneration\System\Capabilities\ConvertDataObjectShapeToJsonSchema\ConvertDataObjectShapeToJsonSchema;
use Avax\Components\API\SchemaGeneration\System\Capabilities\ReadDataObjectShape\ReadDataObjectShape;
use Avax\Components\API\SchemaGeneration\System\Capabilities\ResolveRequestSchema\ResolveRequestSchema;
use Avax\Components\API\SchemaGeneration\System\Capabilities\ResolveResponseSchema\ResolveResponseSchema;
use Avax\Components\API\SchemaGeneration\System\Configuration\SchemaGenerationServiceProvider;
use Avax\Components\API\SchemaGeneration\System\Flows\GenerateRequestSchema\GenerateRequestSchema;
use Avax\Components\API\SchemaGeneration\System\Flows\GenerateResponseSchema\GenerateResponseSchema;
use Avax\Components\API\SchemaGeneration\System\Flows\GenerateSchemaFromDataObject\GenerateSchemaFromDataObject;
use Avax\Components\API\SchemaGeneration\System\Flows\ValidatePayloadAgainstSchema\ValidatePayloadAgainstSchema;
use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\DataStack\DataTransfer\System\Capabilities\DataShapeInspection\InspectDataShape;
use PHPUnit\Framework\TestCase;

final class SchemaGenerationServiceProviderTest extends TestCase
{
    private SimpleContainer $container;
    private SchemaGenerationServiceProvider $provider;

    protected function setUp(): void
    {
        $this->container = new SimpleContainer();
        $this->provider = new SchemaGenerationServiceProvider();
        $this->provider->register($this->container);
        $this->provider->boot($this->container);
    }

    public function test_inspect_data_shape_resolves(): void
    {
        $capability = $this->container->get(InspectDataShape::class);

        $this->assertInstanceOf(InspectDataShape::class, $capability);
    }

    public function test_read_data_object_shape_resolves(): void
    {
        $capability = $this->container->get(ReadDataObjectShape::class);

        $this->assertInstanceOf(ReadDataObjectShape::class, $capability);
    }

    public function test_convert_data_object_shape_to_json_schema_resolves(): void
    {
        $capability = $this->container->get(ConvertDataObjectShapeToJsonSchema::class);

        $this->assertInstanceOf(ConvertDataObjectShapeToJsonSchema::class, $capability);
    }

    public function test_resolve_request_schema_resolves(): void
    {
        $capability = $this->container->get(ResolveRequestSchema::class);

        $this->assertInstanceOf(ResolveRequestSchema::class, $capability);
    }

    public function test_resolve_response_schema_resolves(): void
    {
        $capability = $this->container->get(ResolveResponseSchema::class);

        $this->assertInstanceOf(ResolveResponseSchema::class, $capability);
    }

    public function test_generate_schema_from_data_object_resolves(): void
    {
        $flow = $this->container->get(GenerateSchemaFromDataObject::class);

        $this->assertInstanceOf(GenerateSchemaFromDataObject::class, $flow);
    }

    public function test_generate_request_schema_resolves(): void
    {
        $flow = $this->container->get(GenerateRequestSchema::class);

        $this->assertInstanceOf(GenerateRequestSchema::class, $flow);
    }

    public function test_generate_response_schema_resolves(): void
    {
        $flow = $this->container->get(GenerateResponseSchema::class);

        $this->assertInstanceOf(GenerateResponseSchema::class, $flow);
    }

    public function test_validate_payload_against_schema_resolves(): void
    {
        $flow = $this->container->get(ValidatePayloadAgainstSchema::class);

        $this->assertInstanceOf(ValidatePayloadAgainstSchema::class, $flow);
    }
}
