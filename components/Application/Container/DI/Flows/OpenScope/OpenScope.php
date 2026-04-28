<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\DI\Flows\OpenScope;

use Avax\Components\Application\Container\DI\Capabilities\Resolution\ServiceResolver;
use Avax\Components\Application\Container\DI\Capabilities\Runtime\Scopes\ScopeKind;

/**
 * Public scope-entry flow.
 */
final readonly class OpenScope
{
    private ServiceResolver $resolver;

    public function __construct(
        ServiceResolver $resolver
    )
    {
        $this->resolver = $resolver;
    }

    /**
     * Opens one new scope frame.
     */
    public function open(string|null $kind = null, string $scopeId = '') : void
    {
        $kind ??= ScopeKind::OPERATION;
        $this->resolver->openScope(kind: $kind, scopeId: $scopeId);
    }
}
