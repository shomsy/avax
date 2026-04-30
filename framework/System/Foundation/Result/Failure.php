<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Result;

final readonly class Failure
{
    public function __construct(
        public string $code,
        public string $message,
        public array  $context = [],
    ) {}
}
