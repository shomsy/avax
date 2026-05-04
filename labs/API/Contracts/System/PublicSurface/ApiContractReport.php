<?php

declare(strict_types=1);

namespace Avax\Labs\API\Contracts\System\PublicSurface;

use Avax\Labs\API\Contracts\System\Capabilities\BreakingChanges\BreakingChangeReport;

final class ApiContractReport
{
    /**
     * @param list<string> $errors
     * @param list<string> $warnings
     */
    public function __construct(
        public readonly ApiContract               $contract,
        public readonly array                     $errors = [],
        public readonly array                     $warnings = [],
        public readonly BreakingChangeReport|null $breakingChanges = null,
    )
    {
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    public function hasBreakingChanges(): bool
    {
        return $this->breakingChanges?->hasBreakingChanges() ?? false;
    }
}
