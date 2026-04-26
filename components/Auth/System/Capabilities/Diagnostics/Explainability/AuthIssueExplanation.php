<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Diagnostics\Explainability;

use SensitiveParameter;

final readonly class AuthIssueExplanation
{
    /**
     * @param list<string>               $resolution
     * @param array<string, scalar|null> $context
     */
    public function __construct(
        #[SensitiveParameter] public string $code,
        public string                       $message,
        public string                       $meaning,
        public array                        $resolution,
        public array                        $context = []
    ) {}
}
