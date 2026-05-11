<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\StoreSagaState;

use DateTimeImmutable;
use DateTimeInterface;
use Random\RandomException;

final readonly class SagaEvent
{
    public array $payload;

    private function __construct(
        public string            $id,
        public string            $type,
        public string            $sagaId,
        public string            $sagaName,
        public string|null $stepName, array|null $payload,
        public DateTimeImmutable $occurredAt,
    )
    {
        $payload       ??= [];
        $this->payload = $payload;
    }

    public static function started(
        string $sagaId,
        string $sagaName,
        array  $initialData = [],
    ) : self
    {
        return self::create(sagaId: $sagaId, sagaName: $sagaName, type: 'saga_started', payload: $initialData);
    }

    public static function create(
        string  $sagaId,
        string  $sagaName,
        string $type, array|null $payload = null, string|null $stepName = null,
    ) : self
    {
        $payload ??= [];

        return new self(
            id        : self::generateId(),
            type      : $type,
            sagaId    : $sagaId,
            sagaName  : $sagaName,
            stepName  : $stepName,
            payload   : $payload,
            occurredAt: new DateTimeImmutable(),
        );
    }

    /**
     * @throws RandomException
     */
    private static function generateId() : string
    {
        return sprintf('evt_%s_%s', date('YmdHis'), bin2hex(random_bytes(6)));
    }

    public static function stepCompleted(
        string $sagaId,
        string $sagaName,
        string $stepName,
        array  $output = [],
    ) : self
    {
        return self::create(sagaId: $sagaId, sagaName: $sagaName, type: 'step_completed', payload: $output, stepName: $stepName);
    }

    public static function stepFailed(
        string $sagaId,
        string $sagaName,
        string $stepName,
        string $error,
    ) : self
    {
        return self::create(sagaId: $sagaId, sagaName: $sagaName, type: 'step_failed', payload: ['error' => $error], stepName: $stepName);
    }

    public static function completed(
        string $sagaId,
        string $sagaName,
        array  $finalData = [],
    ) : self
    {
        return self::create(sagaId: $sagaId, sagaName: $sagaName, type: 'saga_completed', payload: $finalData);
    }

    public static function compensated(
        string $sagaId,
        string $sagaName,
    ) : self
    {
        return self::create(sagaId: $sagaId, sagaName: $sagaName, type: 'saga_compensated');
    }

    public function toArray() : array
    {
        return [
            'id'          => $this->id,
            'type'        => $this->type,
            'saga_id'     => $this->sagaId,
            'saga_name'   => $this->sagaName,
            'step_name'   => $this->stepName,
            'payload'     => $this->payload,
            'occurred_at' => $this->occurredAt->format(format: DateTimeInterface::ISO8601),
        ];
    }
}
