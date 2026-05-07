<?php

declare(strict_types=1);

namespace Avax\Components\API\Surface\System\PublicSurface;

use Avax\Components\API\Surface\System\Capabilities\Compatibility\CompatibilityReport;

final class ApiSurfaceReport
{
    /**
     * @param list<string> $errors
     * @param list<string> $warnings
     */
    public function __construct(
        public readonly ApiSurfaceDefinition $surface,
        public readonly array                $errors = [],
        public readonly array                $warnings = [],
        public readonly ?CompatibilityReport $compatibility = null,
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
