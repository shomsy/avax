<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\System\System\Capabilities\DataLayer\AccessPersistentData;

final readonly class AccessPersistentData
{
    public function __construct(private object $databaseRuntime) {}

    public function read(PersistentDataRequest $persistentDataRequest) : PersistentDataResult
    {
        return $this->raw(request: $persistentDataRequest);
    }

    public function raw(PersistentDataRequest $persistentDataRequest) : PersistentDataResult
    {
        $this->validateParameters(request: $persistentDataRequest);

        if (method_exists($this->databaseRuntime, 'executeRawDataQuery')) {
            /** @var PersistentDataResult $result */
            $result = $this->databaseRuntime->executeRawDataQuery($persistentDataRequest);

            return $result;
        }

        if (method_exists($this->databaseRuntime, 'query')) {
            $rows = $this->databaseRuntime->query()
                ->statement($persistentDataRequest->statement)
                ->bindings($persistentDataRequest->parameters)
                ->get();

            return new PersistentDataResult(rows: $rows);
        }

        throw new PersistentDataFailure(message: 'Database runtime does not support raw queries');
    }

    private function validateParameters(PersistentDataRequest $persistentDataRequest) : void
    {
        foreach (array_keys($persistentDataRequest->parameters) as $key) {
            if (is_int($key)) {
                throw new PersistentDataFailure(message: 'Positional ? parameters are not accepted; use named parameters');
            }
        }
    }

    public function transaction(callable $callback, string $connectionName = 'primary') : mixed
    {
        if (method_exists($this->databaseRuntime, 'runDataTransaction')) {
            return $this->databaseRuntime->runDataTransaction(callback: $callback, connectionName: $connectionName);
        }

        if (method_exists($this->databaseRuntime, 'transactions')) {
            return $this->databaseRuntime->transactions()->run(callback: $callback, connectionName: $connectionName);
        }

        throw new PersistentDataFailure(message: 'Database runtime does not support transactions');
    }
}
