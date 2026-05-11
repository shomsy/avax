<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\Worker;

use DateTimeImmutable;

final class WorkerLifecycle
{
    /**
     * @var list<string>
     */
    private array $handledRequestIds = [];

    private DateTimeImmutable|null $stoppedAt = null;

    public function __construct(
        private readonly string $runtimeName,
        private readonly DateTimeImmutable $startedAt,
    ) {
    }

    public function runtimeName(): string
    {
        return $this->runtimeName;
    }

    public function startedAt(): DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function stoppedAt() : DateTimeImmutable|null
    {
        return $this->stoppedAt;
    }

    public function recordHandledRequest(string $requestId): void
    {
        $this->handledRequestIds[] = $requestId;
    }

    /**
     * @return list<string>
     */
    public function handledRequestIds(): array
    {
        return $this->handledRequestIds;
    }

    public function handledRequestsCount(): int
    {
        return count($this->handledRequestIds);
    }

    public function stop(DateTimeImmutable $stoppedAt): void
    {
        $this->stoppedAt = $stoppedAt;
    }
}
