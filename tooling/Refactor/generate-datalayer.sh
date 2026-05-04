mkdir -p components/DataLayer/AccessPersistentData
mkdir -p components/DataLayer/CommitDataChanges
mkdir -p components/DataLayer/ConfigureDataLayer

# Value Objects
cat > components/DataLayer/AccessPersistentData/PersistentDataRequest.php << 'PHP'
<?php
declare(strict_types=1);
namespace Avax\DataLayer\AccessPersistentData;

final class PersistentDataRequest
{
    public function __construct(
        public string $statement,
        public array $parameters = []
    ) {}
}
PHP

cat > components/DataLayer/AccessPersistentData/PersistentDataResult.php << 'PHP'
<?php
declare(strict_types=1);
namespace Avax\DataLayer\AccessPersistentData;

final class PersistentDataResult
{
    public function __construct(
        public array $rows,
        public int $affectedRows = 0
    ) {}
}
PHP

cat > components/DataLayer/AccessPersistentData/PersistentDataFailure.php << 'PHP'
<?php
declare(strict_types=1);
namespace Avax\DataLayer\AccessPersistentData;

use RuntimeException;

final class PersistentDataFailure extends RuntimeException {}
PHP

# CommitDataChanges value objects
cat > components/DataLayer/CommitDataChanges/DataTransactionPolicy.php << 'PHP'
<?php
declare(strict_types=1);
namespace Avax\DataLayer\CommitDataChanges;

final class DataTransactionPolicy
{
    public function __construct(
        public int $maxAttempts = 3,
        public bool $retryTransientFailures = true,
        public bool $idempotent = false
    ) {}
}
PHP

cat > components/DataLayer/CommitDataChanges/DataTransactionFailure.php << 'PHP'
<?php
declare(strict_types=1);
namespace Avax\DataLayer\CommitDataChanges;

use RuntimeException;

final class DataTransactionFailure extends RuntimeException {}
PHP

# Config
cat > components/DataLayer/ConfigureDataLayer/DataLayerConfig.php << 'PHP'
<?php
declare(strict_types=1);
namespace Avax\DataLayer\ConfigureDataLayer;

final class DataLayerConfig
{
    public function __construct(
        private object $databaseRuntime
    ) {}

    public function databaseRuntime(): object
    {
        return $this->databaseRuntime;
    }
}
PHP

cat > components/DataLayer/ConfigureDataLayer/RegisterDataLayerRuntime.php << 'PHP'
<?php
declare(strict_types=1);
namespace Avax\DataLayer\ConfigureDataLayer;

use Avax\DataLayer\ConfigureDataLayer\DataLayerConfig;

final class RegisterDataLayerRuntime
{
    public function register(object $databaseRuntime): DataLayerConfig
    {
        return new DataLayerConfig(databaseRuntime: $databaseRuntime);
    }
}
PHP

cat > components/DataLayer/ConfigureDataLayer/ResolveDataLayerRuntime.php << 'PHP'
<?php
declare(strict_types=1);
namespace Avax\DataLayer\ConfigureDataLayer;

use Avax\DataLayer\ConfigureDataLayer\DataLayerConfig;

final class ResolveDataLayerRuntime
{
    public function resolve(DataLayerConfig $config): object
    {
        return $config->databaseRuntime();
    }
}
PHP

cat > components/DataLayer/ConfigureDataLayer/DataLayerConfigurationFailure.php << 'PHP'
<?php
declare(strict_types=1);
namespace Avax\DataLayer\ConfigureDataLayer;

use RuntimeException;

final class DataLayerConfigurationFailure extends RuntimeException {}
PHP

# AccessPersistentData implementation
cat > components/DataLayer/AccessPersistentData/AccessPersistentData.php << 'PHP'
<?php
declare(strict_types=1);
namespace Avax\DataLayer\AccessPersistentData;

use Avax\DataLayer\AccessPersistentData\PersistentDataFailure;
use Avax\DataLayer\AccessPersistentData\PersistentDataResult;
use Avax\DataLayer\AccessPersistentData\PersistentDataRequest;
use Throwable;

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
PHP

# CommitDataChanges implementation
cat > components/DataLayer/CommitDataChanges/CommitDataChanges.php << 'PHP'
<?php
declare(strict_types=1);
namespace Avax\DataLayer\CommitDataChanges;

use Avax\DataLayer\CommitDataChanges\DataTransactionFailure;
use Avax\DataLayer\CommitDataChanges\DataTransactionPolicy;
use Throwable;

final class CommitDataChanges
{
    public function __construct(
        private object $databaseRuntime
    ) {}

    public function open(): object
    {
        if (method_exists($this->databaseRuntime, 'begin')) {
            $this->databaseRuntime->begin();
        } elseif (method_exists($this->databaseRuntime, 'transactions')) {
            $this->databaseRuntime->transactions()->begin();
        } else {
            throw new DataTransactionFailure(message: 'Cannot begin transaction');
        }
        return (object)['status' => 'open'];
    }

    public function rollback(object $transaction): object
    {
        if (method_exists($this->databaseRuntime, 'rollback')) {
            $this->databaseRuntime->rollback();
        } elseif (method_exists($this->databaseRuntime, 'transactions')) {
            $this->databaseRuntime->transactions()->rollback();
        }
        return (object)['status' => 'rolled_back'];
    }

    public function commit(object $transaction = null): void
    {
        if (method_exists($this->databaseRuntime, 'commit')) {
            $this->databaseRuntime->commit();
        } elseif (method_exists($this->databaseRuntime, 'transactions')) {
            $this->databaseRuntime->transactions()->commit();
        }
    }

    public function retryTransientFailure(callable $work, DataTransactionPolicy $policy): mixed
    {
        if (!$policy->idempotent) {
            throw new DataTransactionFailure(message: 'Work is not idempotent and policy forbids retry');
        }

        $attempt = 0;
        while (true) {
            try {
                $attempt++;
                return $work();
            } catch (Throwable $e) {
                $msg = strtolower($e->getMessage());
                $isTransient = str_contains($msg, 'deadlock') || str_contains($msg, '40001') || str_contains($msg, 'sqlstate');
                if (!$isTransient || !$policy->retryTransientFailures) {
                    throw $e;
                }
                if ($attempt >= $policy->maxAttempts) {
                    throw $e;
                }
                // exponential backoff could be added
                usleep(100000 * $attempt); // 100ms * attempt
            }
        }
    }
}
PHP

# DataLayer main facade
cat > components/DataLayer/DataLayer.php << 'PHP'
<?php
declare(strict_types=1);
namespace Avax\DataLayer;

use Avax\DataLayer\AccessPersistentData\AccessPersistentData;
use Avax\DataLayer\CommitDataChanges\CommitDataChanges;
use Avax\DataLayer\ConfigureDataLayer\DataLayerConfig;
use Avax\DataLayer\ConfigureDataLayer\RegisterDataLayerRuntime;

final class DataLayer
{
    private AccessPersistentData $access;
    private CommitDataChanges $commit;
    private DataLayerConfig $config;

    private function __construct(AccessPersistentData $access, CommitDataChanges $commit, DataLayerConfig $config)
    {
        $this->access = $access;
        $this->commit = $commit;
        $this->config = $config;
    }

    public static function fromDatabaseRuntime(object $databaseRuntime): self
    {
        $config = (new RegisterDataLayerRuntime())->register(databaseRuntime: $databaseRuntime);
        $access = new AccessPersistentData(databaseRuntime: $databaseRuntime);
        $commit = new CommitDataChanges(databaseRuntime: $databaseRuntime);
        return new self(access: $access, commit: $commit, config: $config);
    }

    public function access(): AccessPersistentData
    {
        return $this->access;
    }

    public function commit(): CommitDataChanges
    {
        return $this->commit;
    }

    public function configuration(): DataLayerConfig
    {
        return $this->config;
    }
}
PHP

echo "DataLayer component created.\n"
