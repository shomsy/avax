<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataLayer\AccessPersistentData;

use Avax\Components\DataStack\Database\AccessPersistentData\PersistentDataFailure;
use Avax\Components\DataStack\Database\AccessPersistentData\PersistentDataRequest;
use Avax\Components\DataStack\Database\AccessPersistentData\PersistentDataResult;
use Avax\Components\DataStack\Database\DataLayer;
use Avax\Tests\TestCase;

final class AccessPersistentDataTest extends TestCase
{
    public function test_raw_query_requires_named_parameters() : void
    {
        $dataLayer = DataLayer::fromDatabaseRuntime(databaseRuntime: new FakeDataRuntime);

        $this->expectException(PersistentDataFailure::class);
        $this->expectExceptionMessage('named parameter');

        $dataLayer->access()->raw(request: new PersistentDataRequest(statement: 'select * from users'));
    }

    public function test_raw_query_rejects_positional_parameters() : void
    {
        $dataLayer = DataLayer::fromDatabaseRuntime(databaseRuntime: new FakeDataRuntime);

        $this->expectException(PersistentDataFailure::class);
        $this->expectExceptionMessage('Positional ? parameters are not accepted');

        $dataLayer->access()->raw(request: new PersistentDataRequest(
                                               statement : 'select * from users where id = ?',
                                               parameters: ['id' => 10],
                                           ));
    }

    public function test_read_path_hands_named_request_to_runtime() : void
    {
        $runtime = new FakeDataRuntime;
        $dataLayer = DataLayer::fromDatabaseRuntime(databaseRuntime: $runtime);

        $result = $dataLayer->access()->read(request: new PersistentDataRequest(
                                                          statement : 'select * from users where id = :id',
                                                          parameters: ['id' => 10],
                                                      ));

        $this->assertSame([['id' => 10]], $result->rows);
        $this->assertSame('select * from users where id = :id', $runtime->lastRequest?->statement);
    }

    public function test_transaction_boundary_is_explicitly_handed_to_runtime() : void
    {
        $runtime = new FakeDataRuntime;
        $dataLayer = DataLayer::fromDatabaseRuntime(databaseRuntime: $runtime);

        $result = $dataLayer->access()->transaction(
            callback      : static fn (string $connectionName) : string => 'ran-on-' . $connectionName,
            connectionName: 'primary',
        );

        $this->assertSame('ran-on-primary', $result);
        $this->assertSame('primary', $runtime->lastTransactionConnection);
    }
}

final class FakeDataRuntime
{
    public ?PersistentDataRequest $lastRequest = null;

    public ?string $lastTransactionConnection = null;

    public function executeRawDataQuery(PersistentDataRequest $request) : PersistentDataResult
    {
        $this->lastRequest = $request;

        return new PersistentDataResult(rows: [['id' => $request->parameters['id'] ?? null]]);
    }

    public function runDataTransaction(callable $callback, ?string $connectionName = null) : mixed
    {
        $this->lastTransactionConnection = $connectionName;

        return $callback($connectionName);
    }
}
