<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\FailureRecording;

final readonly class FailureRecord
{
    public function __construct(
        public string $workflow,
        public string $message,
        public string $type,
    ) {}
}
