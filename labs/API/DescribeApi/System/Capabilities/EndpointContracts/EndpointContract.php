<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Capabilities\EndpointContracts;

use Avax\Labs\API\DescribeApi\System\Capabilities\AuthContracts\RequiredAuthentication;
use Avax\Labs\API\DescribeApi\System\Capabilities\AuthContracts\RequiredPermission;
use Avax\Labs\API\DescribeApi\System\Capabilities\RequestContracts\RequestDtoContract;
use Avax\Labs\API\DescribeApi\System\Capabilities\ResponseContracts\ErrorResponseContract;
use Avax\Labs\API\DescribeApi\System\Capabilities\ResponseContracts\ResponseDtoContract;

final class EndpointContract
{
    /**
     * @param  list<ErrorResponseContract>  $errorResponses
     * @param  list<RequiredPermission>  $requiredPermissions
     */
    public function __construct(
        public readonly string $path,
        public readonly string $method,
        public readonly string $summary,
        public readonly ?string $description,
        public readonly EndpointVersion $version,
        public readonly ?EndpointDeprecation $deprecation,
        public readonly ?RequestDtoContract $requestBody,
        public readonly ?ResponseDtoContract $successResponse,
        public readonly ?RequiredAuthentication $authentication,
        public readonly ?string $operationId,
        public readonly array $errorResponses = [],
        public readonly array $requiredPermissions = [],
    ) {
    }

    public function requiresAuthentication(): bool
    {
        return $this->authentication !== null;
    }

    public function requiresPermissions(): bool
    {
        return $this->requiredPermissions !== [];
    }

    public function getErrorResponse(int $statusCode) : ErrorResponseContract|null
    {
        foreach ($this->errorResponses as $response) {
            if ($response->statusCode === $statusCode) {
                return $response;
            }
        }

        return null;
    }
}
