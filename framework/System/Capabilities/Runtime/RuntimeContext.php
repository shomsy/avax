<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime;

use Avax\Framework\System\Capabilities\RequestScope\RequestScopeId;
use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;

final class RuntimeContext implements ResettableState
{
    private RuntimeRequest|null $currentRequest = null;

    private RequestScopeId|null $currentScopeId = null;

    private RuntimeResult|null $lastResult = null;

    public function hasActiveRequest(): bool
    {
        return $this->currentRequest !== null;
    }

    public function startRequest(RequestScopeId $scopeId, RuntimeRequest $request): void
    {
        if ($this->hasActiveRequest()) {
            throw new FrameworkMisconfigured(message: 'Runtime context already has an active request.');
        }

        $this->currentScopeId = $scopeId;
        $this->currentRequest = $request;
    }

    public function finishRequest(RuntimeResult $result): void
    {
        $this->lastResult     = $result;
        $this->currentRequest = null;
        $this->currentScopeId = null;
    }

    public function recordResult(RuntimeResult $result): void
    {
        $this->lastResult = $result;
    }

    public function currentRequest(): RuntimeRequest|null
    {
        return $this->currentRequest;
    }

    public function currentScopeId(): RequestScopeId|null
    {
        return $this->currentScopeId;
    }

    public function lastResult(): RuntimeResult|null
    {
        return $this->lastResult;
    }

    public function resetState(): void
    {
        $this->currentRequest = null;
        $this->currentScopeId = null;
        $this->lastResult     = null;
    }
}
