<?php

declare(strict_types=1);

namespace Avax\Container\Capabilities\Policies\Decisions;

use SensitiveParameter;

/**
 * Non-throwing policy decision that blocks resolution.
 */
final readonly class ResolutionBlocked
{
    public function __construct(
        public string                       $message,
        #[SensitiveParameter] public string $code = 'policy.blocked',
        public mixed                        $context = null
    ) {}
}
