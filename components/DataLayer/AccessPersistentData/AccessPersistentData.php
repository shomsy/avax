<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccessPersistentData;

/**
 * AccessPersistentData - application entry point for explicit read, write, raw query, and transaction handoffs.
 */
final readonly class AccessPersistentData
{
    public function __construct(
        private UseDatabaseRuntime  $useDatabaseRuntime,
        private ReadPersistentData  $readPersistentData,
        private WritePersistentData $writePersistentData,
        private ExecuteRawDataQuery $executeRawDataQuery,
        private RunDataTransaction  $runDataTransaction
    ) {}

    public function databaseRuntime() : object
    {
        return $this->useDatabaseRuntime->databaseRuntime();
    }

    public function read(PersistentDataRequest $request) : PersistentDataResult
    {
        return $this->readPersistentData->read(request: $request);
    }

    public function write(PersistentDataRequest $request) : PersistentDataResult
    {
        return $this->writePersistentData->write(request: $request);
    }

    public function raw(PersistentDataRequest $request) : PersistentDataResult
    {
        return $this->executeRawDataQuery->execute(request: $request);
    }

    public function transaction(callable $callback, string|null $connectionName = null) : mixed
    {
        return $this->runDataTransaction->run(callback: $callback, connectionName: $connectionName);
    }
}
