<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime;

use Avax\Framework\System\Capabilities\RequestScope\RequestScopeId;
use Avax\Framework\System\Capabilities\StateReset\ResettableState;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;

final class RuntimeContext implements ResettableState
{
    private ?RuntimeRequest $runtimeRequest = null;

    private ?RequestScopeId $requestScopeId = null;

    private ?RuntimeResult $runtimeResult = null;

    public function hasActiveRequest() : bool
    {
        return $this->runtimeRequest instanceof RuntimeRequest;
    }

    public function startRequest(RequestScopeId $requestScopeId, RuntimeRequest $runtimeRequest) : void
    {
        if ($this->hasActiveRequest()) {
            throw new FrameworkMisconfigured(message: 'Runtime context already has an active request.');
        }

        $this->requestScopeId = $requestScopeId;
        $this->runtimeRequest = $runtimeRequest;
    }

    public function finishRequest(RuntimeResult $runtimeResult) : void
    {
        $this->runtimeResult  = $runtimeResult;
        $this->runtimeRequest = null;
        $this->requestScopeId = null;
    }

    public function recordResult(RuntimeResult $runtimeResult) : void
    {
        $this->runtimeResult = $runtimeResult;
    }

    public function currentRequest() : ?RuntimeRequest
    {
        return $this->runtimeRequest;
    }

    public function currentScopeId() : ?RequestScopeId
    {
        return $this->requestScopeId;
    }

    public function lastResult() : ?RuntimeResult
    {
        return $this->runtimeResult;
    }

    public function resetState() : void
    {
        $this->runtimeRequest = null;
        $this->requestScopeId = null;
        $this->runtimeResult  = null;
    }
}
