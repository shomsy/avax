<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Capacity\System\PublicSurface;

final readonly class CapacityReport
{
    public function __construct(
        public CapacityModel  $model,
        public CapacityBudget $budget,
        public array          $violations = [],
    )
    {
    }

    public function isValid(): bool
    {
        return empty($this->violations);
    }
}
