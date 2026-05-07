<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\Flows\AnalyzeApiEvolution;

use Avax\Components\API\ApiBlueprint\System\Capabilities\Compatibility\CompatibilityChange;
use Avax\Components\API\ApiBlueprint\System\Capabilities\Compatibility\CompatibilityChangeDetector;
use Avax\Components\API\ApiBlueprint\System\Capabilities\Compatibility\CompatibilityChangeType;
use Avax\Components\API\ApiBlueprint\System\Capabilities\Compatibility\CompatibilityReport;
use Avax\Components\API\ApiBlueprint\System\PublicSurface\ApiBlueprintDefinition;

final class AnalyzeApiEvolution
{
    public function __construct(private readonly CompatibilityChangeDetector $compatibilityChangeDetector) {}

    public function analyze(ApiBlueprintDefinition $oldSurface, ApiBlueprintDefinition $newSurface) : CompatibilityReport
    {
        $changes = [];

        foreach ($oldSurface->endpoints as $oldEndpoint) {
            $newEndpoint = $newSurface->findEndpoint($oldEndpoint->path, $oldEndpoint->method);

            if ($newEndpoint === null) {
                $changes[] = new CompatibilityChange(
                    type       : CompatibilityChangeType::REMOVED_ENDPOINT,
                    path       : $oldEndpoint->path,
                    description: 'Endpoint was removed.',
                    before     : $oldEndpoint->method,
                    after      : null,
                );

                continue;
            }

            $changes = [
                ...$changes,
                ...$this->compatibilityChangeDetector->detect(old: $oldEndpoint, new: $newEndpoint)->changes,
            ];
        }

        return new CompatibilityReport($changes);
    }
}
