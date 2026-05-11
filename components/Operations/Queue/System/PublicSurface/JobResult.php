<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\PublicSurface;

final readonly class JobResult
{
    public function __construct(
        public bool $success,
        public mixed $result = null,
        public string|null $error = null,
        public int|null    $attempts = null,
    ) {
    }

    public static function success(mixed $result = null, int $attempts = 1): self
    {
        return new self(success: true, result: $result, attempts: $attempts);
    }

    public static function failure(string $error, int $attempts = 1): self
    {
        return new self(success: false, error: $error, attempts: $attempts);
    }
}
