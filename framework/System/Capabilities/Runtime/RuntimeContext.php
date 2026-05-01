<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime;

use Avax\Framework\System\Capabilities\RequestScope\RequestScopeId;
use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;

final class RuntimeContext implements ResettableState
{
    private ?RuntimeRequest $currentRequest = null;

    private ?RequestScopeId $currentScopeId = null;

    private ?RuntimeResult $lastResult = null;

    public function hasActiveRequest() : bool
    {
        return $this->currentRequest !== null;
    }

    public function startRequest(RequestScopeId $scopeId, RuntimeRequest $request) : void
    {
        if ($this->hasActiveRequest()) {
            throw new FrameworkMisconfigured(message: 'Runtime context already has an active request.');
        }

        $this->currentScopeId = $scopeId;
        $this->currentRequest = $request;
    }

    public function finishRequest(RuntimeResult $result) : void
    {
        $this->lastResult = $result;
        $this->currentRequest = null;
        $this->currentScopeId = null;
    }

    public function recordResult(RuntimeResult $result) : void
    {
        $this->lastResult = $result;
    }

    public function currentRequest() : ?RuntimeRequest
    {
        return $this->currentRequest;
    }

    public function currentScopeId() : ?RequestScopeId
    {
        return $this->currentScopeId;
    }

    public function lastResult() : ?RuntimeResult
    {
        return $this->lastResult;
    }

    public function resetState() : void
    {
        $this->currentRequest = null;
        $this->currentScopeId = null;
        $this->lastResult = null;
    }
}
