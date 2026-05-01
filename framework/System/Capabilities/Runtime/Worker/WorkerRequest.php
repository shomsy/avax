<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\Worker;

use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;

final readonly class WorkerRequest
{
    public function __construct(
        private string $id,
        private RuntimeRequest $runtimeRequest,
    ) {
        if (trim(string: $this->id) === '') {
            throw new FrameworkMisconfigured(message: 'Worker request id cannot be empty.');
        }
    }

    public function id() : string
    {
        return $this->id;
    }

    public function request() : RuntimeRequest
    {
        return $this->runtimeRequest;
    }
}
