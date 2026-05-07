<?php

declare(strict_types=1);

namespace Avax\Components\API\ApiBlueprint\System\PublicSurface;

use Avax\Components\API\ApiBlueprint\System\Capabilities\Compatibility\CompatibilityReport;

final class ApiBlueprintReport
{
    /**
     * @param list<string> $errors
     * @param list<string> $warnings
     */
    public function __construct(
        public readonly ApiBlueprintDefinition $surface,
        public readonly array                  $errors = [],
        public readonly array                  $warnings = [],
        public readonly ?CompatibilityReport   $compatibility = null,
    ) {}

    public function isValid() : bool
    {
        return $this->errors === [];
    }

    public function hasCompatibilityIssues() : bool
    {
        return $this->compatibility?->hasCompatibilityIssues() ?? false;
    }
}
