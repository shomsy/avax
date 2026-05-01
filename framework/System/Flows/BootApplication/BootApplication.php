<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\BootApplication;

use Avax\Framework\System\Capabilities\Runtime\Runtime;
use Avax\Framework\System\Configuration\BuildApplication\ApplicationBuilder;
use Throwable;

final readonly class BootApplication
{
    public function __construct(
        private BuildApplicationState $buildApplicationState = new BuildApplicationState(),
    ) {
    }

    public function boot(ApplicationBuilder $applicationBuilder) : Runtime
    {
        try {
            return $this->buildApplicationState->build(builder: $applicationBuilder);
        } catch (Throwable $throwable) {
            throw ApplicationBootFailed::fromThrowable(throwable: $throwable);
        }
    }
}
