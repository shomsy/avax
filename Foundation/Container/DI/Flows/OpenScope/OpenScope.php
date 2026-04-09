<?php

declare(strict_types=1);

namespace Avax\Container\DI\Flows\OpenScope;

use Avax\Container\DI\Capabilities\Resolution\ServiceResolver;
use Avax\Container\DI\Capabilities\Runtime\Scopes\ScopeKind;

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
    public function open(string|null $kind = null, string $scopeId = '') : void
    {
        $kind ??= ScopeKind::OPERATION;
        $this->resolver->openScope(kind: $kind, scopeId: $scopeId);
    }
}
