<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Persistence\System\Capabilities\ReadOptimization;

final readonly class MaterializedViewStats
{
    public function __construct(
        public string $viewName,
        public int $rowsAffected = 0,
        public float $durationMs = 0.0,
        public float $refreshedAt = 0.0,
        public bool $success = true,
        public ?string $error = null,
    ) {
    }

    public static function failure(string $viewName, string $error): self
    {
        return new self(
            viewName: $viewName,
            refreshedAt: microtime(true),
            success: false,
            error: $error,
        );
    }
}
