<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\EndpointDefinitions;

use Avax\Components\API\Surface\System\Capabilities\Authentication\RequiredAuthentication;
use Avax\Components\API\Surface\System\Capabilities\Authentication\RequiredPermission;
use Avax\Components\API\Surface\System\Capabilities\RequestSchemas\RequestSchema;
use Avax\Components\API\Surface\System\Capabilities\ResponseSchemas\ErrorResponseSchema;
use Avax\Components\API\Surface\System\Capabilities\ResponseSchemas\ResponseSchema;

final class EndpointDefinition
{
    /**
     * @param list<ErrorResponseSchema> $errorResponses
     * @param list<RequiredPermission>  $requiredPermissions
     */
    public function __construct(
        public readonly string                  $path,
        public readonly string                  $method,
        public readonly string                  $summary,
        public readonly ?string                 $description,
        public readonly EndpointVersion         $version,
        public readonly ?EndpointDeprecation    $deprecation,
        public readonly ?RequestSchema          $requestBody,
        public readonly ?ResponseSchema         $successResponse,
        public readonly ?RequiredAuthentication $authentication,
        public readonly ?string                 $operationId,
        public readonly array                   $errorResponses = [],
        public readonly array                   $requiredPermissions = [],
    ) {}

    public function requiresAuthentication() : bool
    {
        return $this->authentication !== null;
    }

    public function requiresPermissions() : bool
    {
        return $this->requiredPermissions !== [];
    }

    public function getErrorResponse(int $statusCode) : ?ErrorResponseSchema
    {
        foreach ($this->errorResponses as $response) {
            if ($response->statusCode === $statusCode) {
                return $response;
            }
        }

        return null;
    }
}
