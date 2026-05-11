<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\PublicSurface;

final readonly class JobId
{
    public function __construct(
        public string $value,
        public string|null $queue = null,
    ) {
    }

    public static function generate(string|null $queue = null) : self
    {
        return new self(
            value: uniqid('job-', true),
            queue: $queue,
        );
    }
}
