<?php

declare(strict_types=1);

namespace Avax\Database\System\Configuration;

use Avax\Database\System\Capabilities\Connections\Connections;
use Avax\Database\System\Capabilities\Connections\ReadConnection\ReadConnection;
use Avax\Database\System\Capabilities\Migrations\Migrations;
use Avax\Database\System\Capabilities\Querying\Grammar\GrammarInterface;
use Avax\Database\System\Capabilities\Querying\Grammar\MySQLGrammar;
use Avax\Database\System\Capabilities\Querying\Querying;
use Avax\Database\System\Capabilities\Telemetry\Config\Config;
use Avax\Database\System\Capabilities\Telemetry\Events\EventBus;
use Avax\Database\System\Capabilities\Telemetry\Events\Subscribers\DatabaseLoggerSubscriber;
use Avax\Database\System\Capabilities\Telemetry\Support\ExecutionScope;
use Avax\Database\System\Capabilities\Telemetry\Telemetry;
use Avax\Database\System\Capabilities\Transactions\Transactions;
use Avax\Database\System\Database;
use Avax\Database\System\DatabaseInterface;
use Psr\Log\LoggerInterface;
use Random\RandomException;

/**
 * Explicit, container-free composition builder for the Database system.
 */
final class DatabaseBuilder
{
    /** @var array<string, mixed> */
    private array $config = [];

    /** @var array<string, mixed> */
    private array $telemetryConfig = [];

    private EventBus|null         $eventBus = null;
    private ExecutionScope|null   $scope    = null;
    private GrammarInterface|null $grammar  = null;
    private LoggerInterface|null  $logger   = null;

    /**
     * @param array<string, mixed> $config
     */
    public function usingConfig(array $config) : self
    {
        $clone         = clone $this;
        $clone->config = $config;

        return $clone;
    }

    /**
     * @param array<string, mixed> $connections
     */
    public function usingConnections(array $connections, string $default = 'default') : self
    {
        $clone         = clone $this;
        $clone->config = [
            'default'     => $default,
            'connections' => $connections,
        ];

        return $clone;
    }

    /**
     * @param array<string, mixed> $telemetryConfig
     */
    public function withTelemetryConfig(array $telemetryConfig) : self
    {
        $clone                  = clone $this;
        $clone->telemetryConfig = $telemetryConfig;

        return $clone;
    }

    public function withEventBus(EventBus $eventBus) : self
    {
        $clone           = clone $this;
        $clone->eventBus = $eventBus;

        return $clone;
    }

    public function withScope(ExecutionScope $scope) : self
    {
        $clone        = clone $this;
        $clone->scope = $scope;

        return $clone;
    }

    public function withGrammar(GrammarInterface $grammar) : self
    {
        $clone          = clone $this;
        $clone->grammar = $grammar;

        return $clone;
    }

    public function withLogger(LoggerInterface $logger) : self
    {
        $clone         = clone $this;
        $clone->logger = $logger;

        return $clone;
    }

    /**
     * @throws RandomException
     */
    public function ready() : DatabaseInterface
    {
        $scope          = $this->scope ?? ExecutionScope::fresh();
        $telemetryState = new Config(items: $this->telemetryConfig);
        $eventBus       = $this->eventBus ?? new EventBus();

        if ($this->logger !== null) {
            $eventBus->registerSubscriber(subscriber: new DatabaseLoggerSubscriber(
                                                          logger: $this->logger,
                                                          config: $telemetryState
                                                      ));
        }

        $connections = new Connections(
            readConnection: new ReadConnection(
                         config  : $this->config,
                         eventBus: $eventBus,
                         scope   : $scope
                     )
        );

        $transactions = new Transactions(connections: $connections);
        $querying    = new Querying(
            connections : $connections,
            transactions: $transactions,
            eventBus    : $eventBus,
            grammar     : $this->grammar ?? new MySQLGrammar(),
            scope       : $scope
        );

        $migrations = new Migrations(
            querying    : $querying,
            transactions: $transactions
        );

        $telemetry = new Telemetry(
            eventBus: $eventBus,
            config  : $telemetryState,
            scope   : $scope
        );

        return new Database(
            connections : $connections,
            querying    : $querying,
            migrations  : $migrations,
            transactions: $transactions,
            telemetry   : $telemetry
        );
    }
}
