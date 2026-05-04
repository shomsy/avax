<?php

declare(strict_types=1);

namespace Avax\Labs\API\Contracts\System\Capabilities\BreakingChanges;

use Avax\Labs\API\Contracts\System\Capabilities\EndpointContracts\EndpointContract;

final class BreakingChangeDetector
{
    public function detect(EndpointContract $old, EndpointContract $new): BreakingChangeReport
    {
        $changes = [];

        if ($old->path !== $new->path) {
            $changes[] = new BreakingChange(
                BreakingChangeType::REMOVED_PATH_PARAM,
                $old->path,
                'Path changed',
                $old->path,
                $new->path,
            );
        }

        if ($old->method !== $new->method) {
            $changes[] = new BreakingChange(
                BreakingChangeType::REMOVED_METHOD,
                $old->path,
                'HTTP method changed',
                $old->method,
                $new->method,
            );
        }

        if ($old->successResponse?->statusCode !== $new->successResponse?->statusCode) {
            $changes[] = new BreakingChange(
                BreakingChangeType::CHANGED_STATUS_CODE,
                $old->path,
                'Success status code changed',
                (string)$old->successResponse?->statusCode,
                (string)$new->successResponse?->statusCode,
            );
        }

        if ($old->authentication !== null && $new->authentication === null) {
            $changes[] = new BreakingChange(
                BreakingChangeType::REMOVED_AUTH_REQUIREMENT,
                $old->path,
                'Authentication removed',
                $old->authentication->type,
                'none',
            );
        }

        return new BreakingChangeReport($changes);
    }
}
