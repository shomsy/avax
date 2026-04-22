<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\ReadSessionValue;

use Avax\HTTP\Session\Core\Lifecycle\SessionEngine;

/**
 * ReadSessionValue - flow owner
 */
final class ReadSessionValue
{
    private SessionEngine $engine;

    public function __construct(SessionEngine $engine)
    {
        $this->engine = $engine;
    }

    public function handle(string $key, mixed $default = null) : mixed
    {
        return $this->engine->get(key: $key, default: $default);
    }
}
