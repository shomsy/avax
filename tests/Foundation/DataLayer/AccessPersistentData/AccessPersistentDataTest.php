<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataLayer\AccessPersistentData;

use Avax\DataLayer\AccessPersistentData\PersistentDataFailure;
use Avax\DataLayer\AccessPersistentData\PersistentDataRequest;
use Avax\DataLayer\AccessPersistentData\PersistentDataResult;
use Avax\DataLayer\DataLayer;
use Avax\Tests\TestCase;

final class AccessPersistentDataTest extends TestCase
{
    public function testRawQueryRequiresNamedParameters() : void
    {
        $dataLayer = DataLayer::fromDatabaseRuntime(databaseRuntime: new FakeDataRuntime());

        $this->expectException(PersistentDataFailure::class);
        $this->expectExceptionMessage('named parameter');

        $dataLayer->access()->raw(request: new PersistentDataRequest(statement: 'select * from users'));
    }

    public function testRawQueryRejectsPositionalParameters() : void
    {
        $dataLayer = DataLayer::fromDatabaseRuntime(databaseRuntime: new FakeDataRuntime());

        $this->expectException(PersistentDataFailure::class);
        $this->expectExceptionMessage('Positional ? parameters are not accepted');

        $dataLayer->access()->raw(request: new PersistentDataRequest(
                                               statement : 'select * from users where id = ?',
                                               parameters: ['id' => 10]
                                           ));
    }

    public function testReadPathHandsNamedRequestToRuntime() : void
    {
        $runtime   = new FakeDataRuntime();
        $dataLayer = DataLayer::fromDatabaseRuntime(databaseRuntime: $runtime);

        $result = $dataLayer->access()->read(request: new PersistentDataRequest(
                                                          statement : 'select * from users where id = :id',
                                                          parameters: ['id' => 10]
                                                      ));

        $this->assertSame([['id' => 10]], $result->rows);
        $this->assertSame('select * from users where id = :id', $runtime->lastRequest?->statement);
    }

    public function testTransactionBoundaryIsExplicitlyHandedToRuntime() : void
    {
        $runtime   = new FakeDataRuntime();
        $dataLayer = DataLayer::fromDatabaseRuntime(databaseRuntime: $runtime);

        $result = $dataLayer->access()->transaction(
            callback      : static fn (string $connectionName) : string => 'ran-on-' . $connectionName,
            connectionName: 'primary'
        );

        $this->assertSame('ran-on-primary', $result);
        $this->assertSame('primary', $runtime->lastTransactionConnection);
    }
}

final class FakeDataRuntime
{
    public PersistentDataRequest|null $lastRequest = null;

    public string|null $lastTransactionConnection = null;

    public function executeRawDataQuery(PersistentDataRequest $request) : PersistentDataResult
    {
        $this->lastRequest = $request;

        return new PersistentDataResult(rows: [['id' => $request->parameters['id'] ?? null]]);
    }

    public function runDataTransaction(callable $callback, string|null $connectionName = null) : mixed
    {
        $this->lastTransactionConnection = $connectionName;

        return $callback($connectionName);
    }
}
