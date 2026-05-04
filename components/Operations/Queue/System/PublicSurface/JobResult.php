<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Queue\System\PublicSurface;

use Avax\Components\Operations\Queue\System\Capabilities\Job\JobDefinition;
use Avax\Components\Operations\Queue\System\Capabilities\Queue\QueueBroker;
use Avax\Components\Operations\Queue\System\Flows\Dispatch\DispatchJob;

final readonly class JobResult
{
    public function __construct(
        public bool    $success,
        public mixed   $result = null,
        public ?string $error = null,
        public ?int    $attempts = null,
    )
    {
    }

    public static function success(mixed $result = null, int $attempts = 1): self
    {
        return new self(success: true, result: $result, attempts: $attempts);
    }

    public static function failure(string $error, int $attempts = 1): self
    {
        return new self(success: false, error: $error, attempts: $attempts);
    }
}
