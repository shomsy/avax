<?php
declare(strict_types=1);

namespace Avax\Components\DataLayer\AccessPersistentData;

use Avax\Components\Persistence\System\Capabilities\Repositories\Repository;
use Avax\Components\Persistence\System\Foundation\Failure\PersistenceFailure;

final class AccessPersistentData
{
    public function __construct(
        private object $databaseRuntime
    ) {}

    public function read(PersistentDataRequest $request) : PersistentDataResult
    {
        return $this->raw(request: $request);
    }

    public function raw(PersistentDataRequest $request) : PersistentDataResult
    {
        $this->validateParameters(request: $request);

        if (method_exists($this->databaseRuntime, 'executeRawDataQuery')) {
            // @phpstan-ignore-next-line
            return $this->databaseRuntime->executeRawDataQuery($request);
        }

        if (method_exists($this->databaseRuntime, 'query')) {
            // @phpstan-ignore-next-line
            $rows = $this->databaseRuntime->query()
                ->statement($request->statement)
                ->bindings($request->parameters)
                ->get();

            return new PersistentDataResult(rows: $rows);
        }

        throw new PersistentDataFailure(message: 'Database runtime does not support raw queries');
    }

    private function validateParameters(PersistentDataRequest $request) : void
    {
        foreach ($request->parameters as $key => $value) {
            if (is_numeric($key)) {
                throw new PersistentDataFailure(message: 'Positional ? parameters are not accepted; use named parameters');
            }
        }
    }

    public function transaction(callable $callback, string $connectionName = 'primary') : mixed
    {
        if (method_exists($this->databaseRuntime, 'runDataTransaction')) {
            // @phpstan-ignore-next-line
            return $this->databaseRuntime->runDataTransaction(callback: $callback, connectionName: $connectionName);
        }
        if (method_exists($this->databaseRuntime, 'transactions')) {
            // @phpstan-ignore-next-line
            return $this->databaseRuntime->transactions()->run(callback: $callback, connectionName: $connectionName);
        }
        throw new PersistentDataFailure(message: 'Database runtime does not support transactions');
    }
}
