<?php

declare(strict_types=1);

namespace Avax\Facade\Facades;

use Avax\Facade\BaseFacade;

/**
 * @deprecated Facades hide dependencies. Prefer explicit DI injection.
 *
 * Facade for accessing the ServerRequest service.
 *
 * @method static mixed input(string $key, mixed $default = null)
 * @method static string method()
 * @method static string path()
 * @method static string|null header(string $name)
 * @method static bool has(string $key)
 * @method static array all()
 */
class Request extends BaseFacade
{
    /**
     * The service key used to resolve the ServerRequest service from the container.
     */
    protected static string $accessor = 'ServerRequest';
}
