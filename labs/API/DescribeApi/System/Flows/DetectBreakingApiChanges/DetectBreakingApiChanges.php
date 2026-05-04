<?php

declare(strict_types=1);

namespace Avax\Labs\API\DescribeApi\System\Flows\DetectBreakingApiChanges;

use Avax\Labs\API\DescribeApi\System\Capabilities\BreakingChanges\BreakingChange;
use Avax\Labs\API\DescribeApi\System\Capabilities\BreakingChanges\BreakingChangeDetector;
use Avax\Labs\API\DescribeApi\System\Capabilities\BreakingChanges\BreakingChangeReport;
use Avax\Labs\API\DescribeApi\System\Capabilities\BreakingChanges\BreakingChangeType;
use Avax\Labs\API\DescribeApi\System\PublicSurface\ApiContract;

final class DetectBreakingApiChanges
{
    public function __construct(private readonly BreakingChangeDetector $breakingChangeDetector)
    {
    }

    public function detect(ApiContract $oldContract, ApiContract $newContract): BreakingChangeReport
    {
        $changes = [];

        foreach ($oldContract->endpoints as $oldEndpoint) {
            $newEndpoint = $newContract->findEndpoint($oldEndpoint->path, $oldEndpoint->method);

            if ($newEndpoint === null) {
                $changes[] = new BreakingChange(
                    type: BreakingChangeType::REMOVED_ENDPOINT,
                    path: $oldEndpoint->path,
                    description: 'Endpoint was removed.',
                    before: $oldEndpoint->method,
                    after: null,
                );

                continue;
            }

            $changes = [
                ...$changes,
                ...$this->breakingChangeDetector->detect(old: $oldEndpoint, new: $newEndpoint)->changes,
            ];
        }

        return new BreakingChangeReport($changes);
    }
}
