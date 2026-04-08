<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Flows;

use Avax\Container\DependencyInjection\Dependencies\Resolution\ServiceResolver;

/**
 * Public scope-entry flow.
 */
final readonly class OpenScope
{
    public function __construct(
        private ServiceResolver $resolver
    ) {}

    /**
     * Opens one new scope frame.
     */
    public function open(string $kind = \Avax\Container\DependencyInjection\Scopes\ScopeKind::OPERATION, string $scopeId = '') : void
    {
        $this->resolver->openScope(kind: $kind, scopeId: $scopeId);
    }
}
