<?php

declare(strict_types=1);

namespace Avax\API\Contracts\System\PublicSurface;

final readonly class ApiContractReport
{
    public function __construct(
        public string $contractId,
        public string $path,
        public string $method,
        public bool   $isValid,
        public array  $violations = [],
        public array  $warnings = [],
    )
    {
    }

    public function isValid(): bool
    {
        return $this->violations === [];
    }

    public function hasBreakingChanges(): bool
    {
        return (bool)array_filter(
            $this->violations,
            static fn($v) => $v['severity'] === 'error',
        );
    }
}