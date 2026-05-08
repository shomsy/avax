<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Capabilities\SerializeWorkPayload;

use Avax\Components\Operations\Parallelism\System\Capabilities\SerializeWorkPayload\Failure\UnserializableWorkException;
use Closure;

final readonly class RejectUnserializableWork
{
    /**
     * @param array<string|int, Closure(): mixed> $work
     *
     * @return array<string|int, Closure(): mixed>
     * @throws UnserializableWorkException
     */
    public function filter(array $work) : array
    {
        $serializer = new SerializeWorkPayload();
        $valid      = [];
        $invalid    = [];

        foreach ($work as $name => $action) {
            if ($serializer->canSerialize($action)) {
                $valid[$name] = $action;
            } else {
                $invalid[] = $name;
            }
        }

        if (count($invalid) > 0) {
            throw new UnserializableWorkException(
                message: 'The following work items cannot be serialized: ' . implode(', ', array_map('strval', $invalid)),
                names  : $invalid,
            );
        }

        return $valid;
    }

    /**
     * @param array<string|int, Closure(): mixed> $work
     *
     * @return list<string|int>
     */
    public function findUnserializable(array $work) : array
    {
        $serializer     = new SerializeWorkPayload();
        $unserializable = [];

        foreach ($work as $name => $action) {
            if (! $serializer->canSerialize($action)) {
                $unserializable[] = $name;
            }
        }

        return $unserializable;
    }
}
