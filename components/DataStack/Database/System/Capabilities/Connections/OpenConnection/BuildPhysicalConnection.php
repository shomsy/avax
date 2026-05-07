<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\OpenConnection;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\Exceptions\ConnectionFailure;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\ValueObjects\ConnectionConfig;
use Avax\Components\DataStack\Database\System\Capabilities\Connections\ValueObjects\Dsn;
use PDO;
use Throwable;

/**
 * Builds one physical database connection from raw config.
 */
final readonly class BuildPhysicalConnection
{
    /**
     * @param  array<string, mixed>  $config
     *
     * @throws ConnectionFailure
     */
    public function from(array $config): DatabaseConnection
    {
        $connectionConfig = ConnectionConfig::from(config: $config);
        $dsn = Dsn::for(
            driver  : $connectionConfig->driver,
            host    : $connectionConfig->host,
            database: $connectionConfig->database,
            charset : $connectionConfig->charset,
        );

        try {
            $pdo = new PDO(
                dsn     : $dsn->toString(),
                username: $connectionConfig->username,
                password: $connectionConfig->password,
                options : [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ],
            );

            return new PdoConnection(
                name: $connectionConfig->name,
                pdo : $pdo,
            );
        } catch (Throwable $throwable) {
            throw new ConnectionFailure(
                name     : $connectionConfig->name,
                message  : sprintf(
                    'Database connection [%s] failed: %s',
                    $connectionConfig->name,
                    $throwable->getMessage(),
                ),
                throwable: $throwable,
            );
        }
    }
}
