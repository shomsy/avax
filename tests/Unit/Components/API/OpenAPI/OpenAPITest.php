<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\API\OpenAPI;

use Avax\Components\API\OpenAPI\System\Configuration\OpenApiConfiguration;
use Avax\Components\API\OpenAPI\System\PublicSurface\OpenAPI;
use Avax\Components\API\OpenAPI\System\PublicSurface\OpenApiDocument;
use Avax\Components\API\Surface\System\Capabilities\Authentication\RequiredAuthentication;
use Avax\Components\API\Surface\System\Capabilities\EndpointDefinitions\EndpointDefinition;
use Avax\Components\API\Surface\System\Capabilities\EndpointDefinitions\EndpointVersion;
use Avax\Components\API\Surface\System\Capabilities\RequestSchemas\RequestSchema;
use Avax\Components\API\Surface\System\Capabilities\ResponseSchemas\ErrorResponseSchema;
use Avax\Components\API\Surface\System\Capabilities\ResponseSchemas\ResponseSchema;
use Avax\Components\API\Surface\System\PublicSurface\ApiSurfaceDefinition;
use PHPUnit\Framework\TestCase;

final class OpenAPITest extends TestCase
{
    public function test_openapi_document_is_generated_from_api_surface() : void
    {
        $document = OpenAPI::fromSurface(
            surface      : new ApiSurfaceDefinition([$this->endpoint()]),
            configuration: new OpenApiConfiguration(
                               title  : 'Users API',
                               version: '2.0.0',
                               baseUrl: 'https://api.example.test',
                           ),
        );

        $payload   = $document->toArray();
        $operation = $document->path('/users')['get'] ?? null;

        self::assertSame('3.1.0', $payload['openapi']);
        self::assertSame('Users API', $document->title());
        self::assertSame('2.0.0', $document->version());
        self::assertIsArray($operation);
        self::assertSame('users.index', $operation['operationId']);
        self::assertSame(['v1'], $operation['tags']);
        self::assertSame([['bearer' => ['users:read']]], $operation['security']);
        self::assertArrayHasKey('requestBody', $operation);
        self::assertArrayHasKey('200', $operation['responses']);
        self::assertArrayHasKey('404', $operation['responses']);
    }

    private function endpoint(?ResponseSchema $successResponse = null) : EndpointDefinition
    {
        return new EndpointDefinition(
            path           : '/users',
            method         : 'GET',
            summary        : 'List users',
            description    : 'Returns a page of users.',
            version        : EndpointVersion::V1,
            deprecation    : null,
            requestBody    : new RequestSchema(
                                 name       : 'UserFilter',
                                 description: 'Filter accepted by the users endpoint.',
                                 properties : ['name' => ['type' => 'string']],
                                 required   : ['name'],
                             ),
            successResponse: $successResponse ?? new ResponseSchema(
            name       : 'UserCollection',
            description: 'User collection response.',
            statusCode : 200,
            properties : ['data' => ['type' => 'array']],
        ),
            authentication : new RequiredAuthentication(
                                 type  : 'bearer',
                                 realm : 'api',
                                 scopes: ['users:read'],
                             ),
            operationId    : 'users.index',
            errorResponses : [
                                 new ErrorResponseSchema(
                                     statusCode : 404,
                                     code       : 'users.not_found',
                                     message    : 'Users not found.',
                                     description: 'The requested users page does not exist.',
                                     details    : null,
                                 ),
                             ],
        );
    }

    public function test_openapi_validation_reports_document_shape_errors() : void
    {
        $valid   = OpenAPI::validate(OpenAPI::fromSurface(new ApiSurfaceDefinition([$this->endpoint()])));
        $invalid = OpenAPI::validate(new OpenApiDocument([
                                                             'openapi' => '3.0.0',
                                                             'info'    => ['title' => '', 'version' => ''],
                                                             'paths'   => [],
                                                         ]));

        self::assertTrue($valid->isValid());
        self::assertFalse($invalid->isValid());
        self::assertSame([
                             'OpenAPI version must be 3.1.0.',
                             'OpenAPI info.title must not be empty.',
                             'OpenAPI info.version must not be empty.',
                             'OpenAPI paths must not be empty.',
                         ], $invalid->errors);
    }

    public function test_openapi_comparison_reports_removed_and_changed_operations() : void
    {
        $oldDocument     = OpenAPI::fromSurface(new ApiSurfaceDefinition([$this->endpoint()]));
        $changedDocument = OpenAPI::fromSurface(new ApiSurfaceDefinition([
                                                                             $this->endpoint(successResponse: new ResponseSchema(
                                                                                                                  name       : 'AcceptedUserCollection',
                                                                                                                  description: 'Accepted user collection.',
                                                                                                                  statusCode : 202,
                                                                                                              )),
                                                                         ]));
        $emptyDocument   = new OpenApiDocument([
                                                   'openapi' => '3.1.0',
                                                   'info'    => ['title' => 'Empty', 'version' => '1.0.0'],
                                                   'paths'   => [],
                                               ]);

        $changed = OpenAPI::compare(oldDocument: $oldDocument, newDocument: $changedDocument);
        $removed = OpenAPI::compare(oldDocument: $oldDocument, newDocument: $emptyDocument);

        self::assertTrue($changed->hasCompatibilityIssues());
        self::assertSame(['GET /users'], $changed->changedOperations);
        self::assertSame(['GET /users'], $removed->removedOperations);
    }

    public function test_openapi_json_and_yaml_renderers_emit_operator_artifacts() : void
    {
        $document = OpenAPI::fromSurface(new ApiSurfaceDefinition([$this->endpoint()]));

        $json = OpenAPI::json(document: $document);
        $yaml = OpenAPI::yaml(document: $document);

        self::assertJson($json);
        self::assertStringContainsString('"openapi": "3.1.0"', $json);
        self::assertStringContainsString('openapi: "3.1.0"', $yaml);
        self::assertStringContainsString('/users:', $yaml);
    }
}
