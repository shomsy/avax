<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\Capabilities\Compatibility;

use Avax\Components\API\Surface\System\Capabilities\EndpointDefinitions\EndpointDefinition;

final class CompatibilityChangeDetector
{
    public function detect(EndpointDefinition $old, EndpointDefinition $new) : CompatibilityReport
    {
        $changes = [];

        if ($old->path !== $new->path) {
            $changes[] = new CompatibilityChange(
                CompatibilityChangeType::REMOVED_PATH_PARAM,
                $old->path,
                'Path changed',
                $old->path,
                $new->path,
            );
        }

        if ($old->method !== $new->method) {
            $changes[] = new CompatibilityChange(
                CompatibilityChangeType::REMOVED_METHOD,
                $old->path,
                'HTTP method changed',
                $old->method,
                $new->method,
            );
        }

        if ($old->successResponse?->statusCode !== $new->successResponse?->statusCode) {
            $changes[] = new CompatibilityChange(
                CompatibilityChangeType::CHANGED_STATUS_CODE,
                $old->path,
                'Success status code changed',
                (string) $old->successResponse?->statusCode,
                (string) $new->successResponse?->statusCode,
            );
        }

        if ($old->authentication !== null && $new->authentication === null) {
            $changes[] = new CompatibilityChange(
                CompatibilityChangeType::REMOVED_AUTH_REQUIREMENT,
                $old->path,
                'Authentication removed',
                $old->authentication->type,
                'none',
            );
        }

        return new CompatibilityReport($changes);
    }
}
