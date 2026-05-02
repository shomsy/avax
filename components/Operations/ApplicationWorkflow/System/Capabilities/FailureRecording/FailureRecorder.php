<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\FailureRecording;

use Throwable;

final class FailureRecorder
{
    /** @var list<FailureRecord> */
    private array $records = [];

    public function record(string $workflow, Throwable $throwable) : FailureRecord
    {
        $failureRecord = new FailureRecord(
            workflow: $workflow,
            message : $throwable->getMessage(),
            type    : $throwable::class,
        );

        $this->records[] = $failureRecord;

        return $failureRecord;
    }

    /**
     * @return list<FailureRecord>
     */
    public function all() : array
    {
        return $this->records;
    }
}

final readonly class FailureRecord
{
    public function __construct(
        public string $workflow,
        public string $message,
        public string $type,
    ) {}
}
