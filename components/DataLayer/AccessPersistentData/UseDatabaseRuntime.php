<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccessPersistentData;

use Avax\DataLayer\ConfigureDataLayer\DataLayerRuntime;
use PDO;
use Throwable;

final readonly class UseDatabaseRuntime
{
    public function __construct(private DataLayerRuntime $runtime) {}

    public function dialect() : string
    {
        $runtime = $this->databaseRuntime();

        if (property_exists($runtime, 'dialect') && is_string($runtime->dialect)) {
            return $runtime->dialect;
        }

        if (method_exists($runtime, 'getDialect')) {
            return $runtime->getDialect();
        }

        $dsn = $this->dsn();
        if (preg_match('/^(\w+):/', $dsn, $matches)) {
            return strtolower($matches[1]);
        }

        return 'mysql';
    }

    public function databaseRuntime() : object
    {
        return $this->runtime->databaseRuntime;
    }

    public function dsn() : string
    {
        $runtime = $this->databaseRuntime();

        if (property_exists($runtime, 'dsn') && is_string($runtime->dsn)) {
            return $runtime->dsn;
        }

        if (method_exists($runtime, 'getDsn')) {
            return $runtime->getDsn();
        }

        return '';
    }

    public function isConnected() : bool
    {
        try {
            $this->connection()->query(query: 'SELECT 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    public function connection(string|null $name = null) : PDO
    {
        $runtime = $this->databaseRuntime();

        if ($runtime instanceof PDO) {
            return $runtime;
        }

        if (method_exists($runtime, 'getConnection')) {
            return $runtime->getConnection($name);
        }

        if (method_exists($runtime, 'connection')) {
            return $runtime->connection($name);
        }

        throw new PersistentDataFailure(
            message: sprintf('Database runtime does not expose PDO connection. Expected PDO or getConnection() method.')
        );
    }
}