<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Parallelism\System\Capabilities\SerializeWorkPayload;

use Closure;

final readonly class SerializeWorkPayload
{
    /**
     * @return array{action: string, payload: string}
     */
    public function serialize(string $name, Closure $action) : array
    {
        $serialized = serialize($action);

        return [
            'action'  => $name,
            'payload' => $serialized,
        ];
    }

    public function canSerialize(Closure $action) : bool
    {
        $result = @serialize($action);

        return $result !== 'N;';
    }
}