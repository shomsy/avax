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

    public function raw(PersistentDataRequest $request): PersistentDataResult
    {
        $this->validateParameters(request: $request);

        if (method_exists($this->databaseRuntime, 'executeRawDataQuery')) {
            /** @var PersistentDataResult $result */
            $result = $this->databaseRuntime->executeRawDataQuery($request);
            return $result;
        }

        if (method_exists($this->databaseRuntime, 'query')) {
            $rows = $this->databaseRuntime->query()
                ->statement($request->statement)
                ->bindings($request->parameters)
                ->get();
            return new PersistentDataResult(rows: $rows);
        }

        throw new PersistentDataFailure(message: 'Database runtime does not support raw queries');
    }

    public function read(PersistentDataRequest $request): PersistentDataResult
    {
        return $this->raw(request: $request);
    }

    public function transaction(callable $callback, string $connectionName = 'primary'): mixed
    {
        if (method_exists($this->databaseRuntime, 'runDataTransaction')) {
            return $this->databaseRuntime->runDataTransaction(callback: $callback, connectionName: $connectionName);
        }
        if (method_exists($this->databaseRuntime, 'transactions')) {
            return $this->databaseRuntime->transactions()->run(callback: $callback, connectionName: $connectionName);
        }
        throw new PersistentDataFailure(message: 'Database runtime does not support transactions');
    }

    private function validateParameters(PersistentDataRequest $request): void
    {
        foreach ($request->parameters as $key => $value) {
            if (is_int($key)) {
                throw new PersistentDataFailure(message: 'Positional ? parameters are not accepted; use named parameters');
            }
        }
    }
}
