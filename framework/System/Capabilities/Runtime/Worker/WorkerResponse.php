<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\Worker;

use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;

final readonly class WorkerResponse
{
    public function __construct(
        private string $requestId,
        private RuntimeResponse $runtimeResponse,
    ) {}

    public static function fromRuntimeResponse(string $requestId, RuntimeResponse $runtimeResponse) : self
    {
        return new self(
            requestId: $requestId,
            response : $runtimeResponse,
        );
    }

    public function requestId() : string
    {
        return $this->requestId;
    }

    public function response() : RuntimeResponse
    {
        return $this->runtimeResponse;
    }
}
