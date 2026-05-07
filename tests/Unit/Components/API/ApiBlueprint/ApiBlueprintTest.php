<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\API\ApiBlueprint;

use Avax\Components\API\ApiBlueprint\System\Capabilities\Authentication\RequiredAuthentication;
use Avax\Components\API\ApiBlueprint\System\Capabilities\Authentication\RequiredPermission;
use Avax\Components\API\ApiBlueprint\System\Capabilities\Compatibility\CompatibilityReport;
use Avax\Components\API\ApiBlueprint\System\Capabilities\EndpointDefinitions\EndpointDefinition;
use Avax\Components\API\ApiBlueprint\System\Capabilities\EndpointDefinitions\EndpointDeprecation;
use Avax\Components\API\ApiBlueprint\System\Capabilities\EndpointDefinitions\EndpointVersion;
use Avax\Components\API\ApiBlueprint\System\Capabilities\RequestSchemas\RequestSchema;
use Avax\Components\API\ApiBlueprint\System\Capabilities\RequestSchemas\RequestValidationResult;
use Avax\Components\API\ApiBlueprint\System\Capabilities\ResponseSchemas\ErrorResponseSchema;
use Avax\Components\API\ApiBlueprint\System\Capabilities\ResponseSchemas\ResponseSchema;
use Avax\Components\API\ApiBlueprint\System\Foundation\Failure\ApiBlueprintInvalid;
use Avax\Components\API\ApiBlueprint\System\PublicSurface\ApiBlueprint;
use Avax\Components\API\ApiBlueprint\System\PublicSurface\ApiBlueprintDefinition;
use PHPUnit\Framework\TestCase;

final class ApiBlueprintTest extends TestCase
{
    public function test_in_memory_api_blueprint_registers_endpoint_and_validates_it() : void
    {
        $blueprint = ApiBlueprint::inMemory();
        $endpoint  = $this->endpoint();

        $blueprint->registerEndpoint(endpoint: $endpoint);

        $definition = $blueprint->describe();
        $report     = $blueprint->validate();

        self::assertSame($endpoint, $definition->findEndpoint('/users', 'GET'));
        self::assertSame([$endpoint], $definition->endpointsForVersion(EndpointVersion::V1));
        self::assertTrue($report->isValid());
        self::assertSame([], $report->errors);
        self::assertSame(['assert GET /users returns 200 for version v1'], $blueprint->compatibilityCheckScenarios());
        self::assertTrue($endpoint->requiresAuthentication());
        self::assertTrue($endpoint->requiresPermissions());
        self::assertTrue($endpoint->authentication?->isBearer());
        self::assertTrue($endpoint->requiredPermissions[0]->matches('users.read'));
        self::assertTrue($endpoint->requestBody?->isRequired('name'));
        self::assertTrue($endpoint->successResponse?->isSuccessful());
        self::assertTrue($endpoint->getErrorResponse(404)?->isClientError());
    }

    private function endpoint(
        string                  $path = '/users',
        string                  $method = 'GET',
        EndpointVersion         $version = EndpointVersion::V1,
        ?EndpointDeprecation    $deprecation = null,
        ?ResponseSchema         $successResponse = null,
        bool                    $includeSuccessResponse = true,
        ?RequiredAuthentication $authentication = null,
        bool                    $includeAuthentication = true,
        string                  $operationId = 'users.index',
    ) : EndpointDefinition
    {
        return new EndpointDefinition(
            path               : $path,
            method             : $method,
            summary            : 'List users',
            description        : 'Returns a page of users.',
            version            : $version,
            deprecation        : $deprecation,
            requestBody        : new RequestSchema(
                                     name       : 'UserFilter',
                                     description: 'Filter accepted by the users endpoint.',
                                     properties : ['name' => ['type' => 'string']],
                                     required   : ['name'],
                                 ),
            successResponse    : $includeSuccessResponse
                                     ? ($successResponse ?? new ResponseSchema(
                                         name       : 'UserCollection',
                                         description: 'User collection response.',
                                         statusCode : 200,
                                         properties : ['data' => ['type' => 'array']],
                                     ))
                                     : null,
            authentication     : $includeAuthentication
                                     ? ($authentication ?? new RequiredAuthentication(
                                         type  : 'bearer',
                                         realm : 'api',
                                         scopes: ['users:read'],
                                     ))
                                     : null,
            operationId        : $operationId,
            errorResponses     : [
                                     new ErrorResponseSchema(
                                         statusCode : 404,
                                         code       : 'users.not_found',
                                         message    : 'Users not found.',
                                         description: 'The requested users page does not exist.',
                                         details    : null,
                                     ),
                                 ],
            requiredPermissions: [
                                     new RequiredPermission(
                                         name       : 'users.read',
                                         description: 'Read user records.',
                                         resource   : 'users',
                                     ),
                                 ],
        );
    }

    public function test_api_blueprint_definition_filters_versions_and_deprecated_endpoints() : void
    {
        $deprecated = $this->endpoint(
            path       : '/legacy-users',
            version    : EndpointVersion::V2,
            deprecation: new EndpointDeprecation(
                             since      : '2.1',
                             warning    : 'Use /users instead.',
                             removeIn   : '3.0',
                             replacement: '/users',
                         ),
        );

        $definition = new ApiBlueprintDefinition([
                                                     $this->endpoint(path: '/users', version: EndpointVersion::V1),
                                                     $deprecated,
                                                 ]);

        self::assertSame([$deprecated], $definition->endpointsForVersion(EndpointVersion::V2));
        self::assertSame([$deprecated], $definition->deprecatedEndpoints());
        self::assertNotNull($deprecated->deprecation);
        $deprecation = $deprecated->deprecation;
        self::assertTrue($deprecation->isDeprecated());
        self::assertSame('critical', $deprecation->severity());
        self::assertFalse(EndpointVersion::V1->isLatest());
        self::assertTrue(EndpointVersion::V3->isLatest());
    }

    public function test_validation_reports_missing_success_response_and_duplicate_operation_ids() : void
    {
        $blueprint = ApiBlueprint::inMemory();

        $blueprint
            ->registerEndpoint(endpoint: $this->endpoint(
                path                  : '/users',
                includeSuccessResponse: false,
                operationId           : 'users.index',
            ))
            ->registerEndpoint(endpoint: $this->endpoint(
                path                  : '/people',
                includeSuccessResponse: false,
                operationId           : 'users.index',
            ));

        $report = $blueprint->validate();

        self::assertFalse($report->isValid());
        self::assertCount(3, $report->errors);
        self::assertStringContainsString('must declare a success response', $report->errors[0]);
        self::assertStringContainsString('Operation id users.index is duplicated.', $report->errors[2]);
    }

    public function test_compatibility_detection_reports_removed_endpoint_as_critical() : void
    {
        $oldSurface = new ApiBlueprintDefinition([$this->endpoint()]);
        $blueprint  = ApiBlueprint::inMemory();

        $report = $blueprint->detectCompatibility(oldSurface: $oldSurface);

        self::assertTrue($report->hasCompatibilityIssues());
        self::assertSame(1, $report->count());
        self::assertCount(1, $report->criticalChanges());
        self::assertSame('/users', $report->criticalChanges()[0]->path);
    }

    public function test_compatibility_detection_reports_status_and_authentication_drift() : void
    {
        $oldSurface = new ApiBlueprintDefinition([
                                                     $this->endpoint(authentication: new RequiredAuthentication(type: 'bearer', realm: 'api')),
                                                 ]);
        $blueprint  = ApiBlueprint::inMemory();
        $blueprint->registerEndpoint(endpoint: $this->endpoint(
            successResponse      : new ResponseSchema(
                                       name       : 'AcceptedUserCollection',
                                       description: 'Accepted user collection.',
                                       statusCode : 202,
                                   ),
            includeAuthentication: false,
        ));

        $report = $blueprint->detectCompatibility(oldSurface: $oldSurface);

        self::assertFalse($report->hasCompatibilityIssues());
        self::assertSame(2, $report->count());
        self::assertSame('Success status code changed', $report->changes[0]->description);
        self::assertSame('Authentication removed', $report->changes[1]->description);
    }

    public function test_request_validation_result_turns_invalid_payload_into_failure() : void
    {
        self::assertNull(RequestValidationResult::valid()->toException());

        $exception = RequestValidationResult::invalid(['name' => 'required'])->toException();

        self::assertInstanceOf(ApiBlueprintInvalid::class, $exception);
        self::assertStringContainsString('Request validation failed', $exception->getMessage());
        self::assertStringContainsString('required', $exception->getMessage());
    }
}
