<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RequestScope;

use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;

final class RequestScopeStore implements ResettableState
{
    private RequestScope|null $currentScope = null;

    /**
     * @throws \Random\RandomException
     */
    public function open(): RequestScope
    {
        if ($this->currentScope !== null && $this->currentScope->isOpen()) {
            throw new FrameworkMisconfigured(message: 'Cannot open a new request scope while another scope is active.');
        }

        $this->currentScope = new RequestScope(id: RequestScopeId::generate());

        return $this->currentScope;
    }

    public function hasCurrent(): bool
    {
        return $this->currentScope !== null && $this->currentScope->isOpen();
    }

    public function current(): RequestScope
    {
        if (! $this->hasCurrent()) {
            throw new RequestScopeNotOpen(message: 'No request scope is currently open.');
        }

        return $this->currentScope ?? throw new RequestScopeNotOpen(message: 'No request scope is currently open.');
    }

    public function closeCurrent(): void
    {
        $this->current()->close();
        $this->currentScope = null;
    }

    public function resetState(): void
    {
        if ($this->hasCurrent()) {
            $this->currentScope?->close();
        }

        $this->currentScope = null;
    }
}
