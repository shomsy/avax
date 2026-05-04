<?php

declare(strict_types=1);

namespace Avax\API\Contracts\System\Configuration;

final readonly class ApiContractsConfiguration
{
    public function __construct(
        public bool   $validateRequests = true,
        public bool   $validateResponses = true,
        public bool   $detectBreakingChanges = true,
        public bool   $generateOpenApi = true,
        public array  $ignoredPaths = [],
        public string $defaultVersion = '1.0.0',
    )
    {
    }

    public static function defaults(): self
    {
        return new self();
    }

    public static function strict(): self
    {
        return new self(
            validateRequests: true,
            validateResponses: true,
            detectBreakingChanges: true,
            generateOpenApi: true,
        );
    }

    public function withOverrides(array $overrides): self
    {
        return new self(
            validateRequests: $overrides['validateRequests'] ?? $this->validateRequests,
            validateResponses: $overrides['validateResponses'] ?? $this->validateResponses,
            detectBreakingChanges: $overrides['detectBreakingChanges'] ?? $this->detectBreakingChanges,
            generateOpenApi: $overrides['generateOpenApi'] ?? $this->generateOpenApi,
            ignoredPaths: $overrides['ignoredPaths'] ?? $this->ignoredPaths,
            defaultVersion: $overrides['defaultVersion'] ?? $this->defaultVersion,
        );
    }
}