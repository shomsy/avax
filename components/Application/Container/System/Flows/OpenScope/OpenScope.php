<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Flows\OpenScope;

use Avax\Components\Application\Container\System\Capabilities\Resolution\ServiceResolver;
use Avax\Components\Application\Container\System\Capabilities\Runtime\Scopes\ScopeKind;

/**
 * Public scope-entry flow.
 */
final readonly class OpenScope
{
    public function __construct(private ServiceResolver $serviceResolver)
    {
    }

    /**
     * Opens one new scope frame.
     */
    public function open(?string $kind = null, string $scopeId = '') : void
    {
        $kind ??= ScopeKind::OPERATION;
        $this->serviceResolver->openScope(kind: $kind, scopeId: $scopeId);
    }
}
