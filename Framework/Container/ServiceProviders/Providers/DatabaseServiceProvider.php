<?php

declare(strict_types=1);

namespace Gemini\Container\ServiceProviders\Providers;

use Gemini\Container\ServiceProviders\ServiceProvider;
use Gemini\Database\{Connection\ConnectionPool, DatabaseConnection, QueryBuilder\QueryBuilder, QueryBuilder\UnitOfWork};
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class DatabaseServiceProvider
 *
 * Registers and configures database-related services inside the dependency injection (DI) container.
 *
 * - ✅ **Connection Pool**: Manages database connections efficiently.
 * - ✅ **Database Connection**: Fetches connections from the pool.
 * - ✅ **Unit of Work**: Handles batch transactions.
 * - ✅ **Query Builder**: Provides a fluent API for building and executing queries.
 */
class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Registers database services in the DI container.
     */
    public function register() : void
    {
        // ✅ Regis  ter ConnectionPool
        $this->dependencyInjector->singleton(
            abstract: ConnectionPool::class,
            concrete: fn() : ConnectionPool => new ConnectionPool(
                config        : [
                                    'connections' => [
                                        'mysql' => [
                                            'connection' => config(key: 'database.connections.mysql.connection'),
                                            'username'   => config(key: 'database.connections.mysql.username'),
                                            'password'   => config(key: 'database.connections.mysql.password'),
                                            'options'    => config(
                                                key:     'database.connections.mysql.options',
                                                default: []
                                            ),
                                        ],
                                    ],
                                ],
                logger        : $this->dependencyInjector->get(LoggerInterface::class),
                maxConnections: 10
            )
        );

        // ✅ Register DatabaseConnection
        $this->dependencyInjector->singleton(
            abstract: DatabaseConnection::class,
            concrete: fn() : DatabaseConnection => new DatabaseConnection(
                connectionPool: $this->dependencyInjector->get(id: ConnectionPool::class),
                logger        : $this->dependencyInjector->get(id: LoggerInterface::class)
            )
        );

        // ✅ Register UnitOfWork (MUST be shared across multiple QueryBuilder instances)
        $this->dependencyInjector->singleton(
            abstract: UnitOfWork::class,
            concrete: fn() : UnitOfWork => new UnitOfWork(
                databaseConnection: $this->dependencyInjector->get(id: DatabaseConnection::class)
            )
        );

        // ✅ Register QueryBuilder
        $this->dependencyInjector->singleton(
            abstract: QueryBuilder::class,
            concrete: fn() : QueryBuilder => new QueryBuilder(
                databaseConnection: $this->dependencyInjector->get(id: DatabaseConnection::class),
                unitOfWork        : $this->dependencyInjector->get(id: UnitOfWork::class),
                logger            : $this->dependencyInjector->get(id: LoggerInterface::class)
            )
        );
    }

    /**
     * Performs an optional database connectivity test during boot.
     *
     * Ensures that the database is accessible before usage.
     */
    public function boot() : void
    {
        try {
            $this->dependencyInjector->get(id: DatabaseConnection::class)->testConnection();
        } catch (Throwable $throwable) {
            // ❌ Log database connection errors
            $logger = $this->dependencyInjector->get(id: LoggerInterface::class);
            $logger->error(
                'Database connection failed in DatabaseServiceProvider::boot() : ' .
                $throwable->getMessage()
            );
        }
    }
}
