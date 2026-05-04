<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\PublicSurface;

use Avax\Components\Operations\Queue\System\Capabilities\Job\JobDefinition;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\QueueBroker;
use Avax\Components\Operations\Queue\System\Flows\Dispatch\DispatchJob;

final readonly class JobId
{
    public function __construct(
        public string  $value,
        public ?string $queue = null,
    )
    {
    }

    public static function generate(?string $queue = null): self
    {
        return new self(
            value: uniqid('job-', true),
            queue: $queue,
        );
    }
}
