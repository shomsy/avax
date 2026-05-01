<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\RequestScope;

use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use Random\RandomException;

final class RequestScopeStore implements ResettableState
{
    private ?RequestScope $requestScope = null;

    /**
     * @throws RandomException
     */
    public function open() : RequestScope
    {
        if ($this->requestScope instanceof RequestScope && $this->requestScope->isOpen()) {
            throw new FrameworkMisconfigured(message: 'Cannot open a new request scope while another scope is active.');
        }

        $this->requestScope = new RequestScope(requestScopeId: RequestScopeId::generate());

        return $this->requestScope;
    }

    public function hasCurrent() : bool
    {
        return $this->requestScope instanceof RequestScope && $this->requestScope->isOpen();
    }

    public function current() : RequestScope
    {
        if (! $this->hasCurrent()) {
            throw new RequestScopeNotOpen(message: 'No request scope is currently open.');
        }

        return $this->requestScope ?? throw new RequestScopeNotOpen(message: 'No request scope is currently open.');
    }

    public function closeCurrent() : void
    {
        $this->current()->close();
        $this->requestScope = null;
    }

    public function resetState() : void
    {
        if ($this->hasCurrent()) {
            $this->requestScope?->close();
        }

        $this->requestScope = null;
    }
}
