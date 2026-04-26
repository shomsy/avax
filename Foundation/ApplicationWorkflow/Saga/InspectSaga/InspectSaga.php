<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\InspectSaga;

use Countable;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
use IteratorAggregate;
use Random\RandomException;
use Traversable;

final class InspectSaga implements IteratorAggregate, Countable
{
    private array $events;

    public function __construct(array $events = [])
    {
        $this->events = $events;
    }

    public static function inMemory() : self
    {
        return new self(events: []);
    }

    public function record(SagaRuntimeEvent $event) : void
    {
        $this->events[] = $event;
    }

    public function getEvents(string $sagaId) : array
    {
        return array_filter(
            $this->events,
            static fn ($e) => $e->sagaId === $sagaId
        );
    }

    public function buildTimeline(string $sagaId) : SagaTimeline
    {
        return SagaTimeline::fromEvents(
            sagaId: $sagaId,
            events: $this->getEvents(sagaId: $sagaId)
        );
    }

    public function buildReport(string $sagaId) : SagaReport
    {
        $events   = $this->getEvents(sagaId: $sagaId);
        $timeline = SagaTimeline::fromEvents(sagaId: $sagaId, events: $events);

        return new SagaReport(
            sagaId     : $sagaId,
            timeline   : $timeline,
            events     : $events,
            generatedAt: new DateTimeImmutable()
        );
    }

    public function traceFailure(string $sagaId) : SagaTimeline|null
    {
        foreach (array_reverse($this->getEvents(sagaId: $sagaId)) as $event) {
            if ($event->type === 'step_failed' || $event->type === 'saga_failed') {
                return $this->buildTimeline(sagaId: $sagaId);
            }
        }

        return null;
    }

    public function count() : int
    {
        return count($this->events);
    }

    public function getIterator() : Traversable
    {
        foreach ($this->events as $event) {
            yield $event;
        }
    }
}

final readonly class SagaRuntimeEvent
{
    public string             $id;
    public string             $sagaId;
    public string             $sagaName;
    public string            $type;
    public string|null       $stepName;
    public array             $payload;
    public DateTimeImmutable $occurredAt;

    private function __construct(
        string            $id,
        string            $sagaId,
        string            $sagaName,
        string            $type,
        string|null       $stepName,
        array             $payload,
        DateTimeImmutable $occurredAt
    )
    {
        $this->id         = $id;
        $this->sagaId     = $sagaId;
        $this->sagaName   = $sagaName;
        $this->type       = $type;
        $this->stepName   = $stepName;
        $this->payload    = $payload;
        $this->occurredAt = $occurredAt;
    }

    public static function create(
        string     $sagaId,
        string     $sagaName,
        string     $type,
        array|null $payload = null,
        string|null $stepName = null
    ) : self
    {
        $payload ??= [];

        return new self(
            id        : self::generateId(),
            sagaId    : $sagaId,
            sagaName  : $sagaName,
            type      : $type,
            stepName  : $stepName,
            payload   : $payload,
            occurredAt: new DateTimeImmutable()
        );
    }

    public static function started(string $sagaId, string $sagaName) : self
    {
        return self::create(sagaId: $sagaId, sagaName: $sagaName, type: 'saga_started');
    }

    public static function stepCompleted(
        string $sagaId,
        string $sagaName,
        string $stepName,
        array  $output = []
    ) : self
    {
        return self::create(sagaId: $sagaId, sagaName: $sagaName, type: 'step_completed', payload: $output, stepName: $stepName);
    }

    public static function stepFailed(
        string $sagaId,
        string $sagaName,
        string $stepName,
        string $error
    ) : self
    {
        return self::create(sagaId: $sagaId, sagaName: $sagaName, type: 'step_failed', payload: ['error' => $error], stepName: $stepName);
    }

    public static function completed(string $sagaId, string $sagaName) : self
    {
        return self::create(sagaId: $sagaId, sagaName: $sagaName, type: 'saga_completed');
    }

    public static function compensated(string $sagaId, string $sagaName) : self
    {
        return self::create(sagaId: $sagaId, sagaName: $sagaName, type: 'saga_compensated');
    }

    public function toArray() : array
    {
        return [
            'id'          => $this->id,
            'saga_id'     => $this->sagaId,
            'saga_name'   => $this->sagaName,
            'type'        => $this->type,
            'step_name'   => $this->stepName,
            'payload'     => $this->payload,
            'occurred_at' => $this->occurredAt->format(format: DateTimeInterface::ISO8601),
        ];
    }

    /**
     * @throws RandomException
     */
    private static function generateId() : string
    {
        return sprintf('evt_%s_%s', date('YmdHis'), bin2hex(random_bytes(6)));
    }
}

final readonly class SagaTimeline
{
    public string  $sagaId;
    public array   $events;
    public array       $steps;
    public string|null $startedAt;
    public string|null $completedAt;
    public float|null  $durationMs;
    public string|null $finalStatus;

    private function __construct(
        string      $sagaId,
        array       $events,
        array       $steps,
        string|null $startedAt,
        string|null $completedAt,
        float|null  $durationMs,
        string|null $finalStatus
    )
    {
        $this->sagaId      = $sagaId;
        $this->events      = $events;
        $this->steps       = $steps;
        $this->startedAt   = $startedAt;
        $this->completedAt = $completedAt;
        $this->durationMs  = $durationMs;
        $this->finalStatus = $finalStatus;
    }

    /**
     * @throws DateMalformedStringException
     */
    public static function fromEvents(string $sagaId, array $events) : self
    {
        $steps       = [];
        $startedAt   = null;
        $completedAt = null;
        $finalStatus = null;

        foreach ($events as $event) {
            if ($event instanceof SagaRuntimeEvent) {
                if ($startedAt === null) {
                    $startedAt = $event->occurredAt->format(format: DateTimeInterface::ISO8601);
                }

                if (in_array($event->type, ['step_completed', 'step_failed'], true)) {
                    $steps[$event->stepName ?? 'unknown'] = [
                        'type' => $event->type,
                        'at' => $event->occurredAt->format(format: DateTimeInterface::ISO8601),
                    ];
                }

                if (in_array($event->type, ['saga_completed', 'saga_compensated', 'saga_failed'], true)) {
                    $completedAt = $event->occurredAt->format(format: DateTimeInterface::ISO8601);
                    $finalStatus = $event->type;
                }
            }
        }

        $durationMs = null;
        if ($startedAt && $completedAt) {
            $start = new DateTimeImmutable(datetime: $startedAt);
            $end   = new DateTimeImmutable(datetime: $completedAt);
            $durationMs = ($end->getTimestamp() - $start->getTimestamp()) * 1000;
        }

        return new self(
            sagaId     : $sagaId,
            events     : $events,
            steps      : $steps,
            startedAt  : $startedAt,
            completedAt: $completedAt,
            durationMs : $durationMs,
            finalStatus: $finalStatus
        );
    }

    public function toArray() : array
    {
        return [
            'saga_id'      => $this->sagaId,
            'steps'        => $this->steps,
            'started_at'   => $this->startedAt,
            'completed_at' => $this->completedAt,
            'duration_ms'  => $this->durationMs,
            'final_status' => $this->finalStatus,
        ];
    }
}

final readonly class SagaReport
{
    public string             $sagaId;
    public SagaTimeline       $timeline;
    public array              $events;
    public DateTimeImmutable $generatedAt;

    private function __construct(
        string             $sagaId,
        SagaTimeline       $timeline,
        array              $events,
        DateTimeImmutable $generatedAt
    )
    {
        $this->sagaId      = $sagaId;
        $this->timeline    = $timeline;
        $this->events      = $events;
        $this->generatedAt = $generatedAt;
    }

    public function toArray() : array
    {
        return [
            'saga_id'      => $this->sagaId,
            'timeline'     => $this->timeline->toArray(),
            'event_count'  => count($this->events),
            'generated_at' => $this->generatedAt->format(format: DateTimeInterface::ISO8601),
        ];
    }
}