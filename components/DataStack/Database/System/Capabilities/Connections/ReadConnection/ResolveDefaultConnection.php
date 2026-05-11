<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Connections\ReadConnection;

/**
 * Resolves which connection name should be used for one request.
 */
final readonly class ResolveDefaultConnection
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(private array $config)
    {
    }

    public function resolve(string|null $connectionName = null) : string
    {
        return $connectionName ?? $this->config['default'] ?? 'mysql';
    }
}
