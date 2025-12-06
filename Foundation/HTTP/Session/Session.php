<?php

declare(strict_types=1);

namespace Avax\HTTP\Session;

use Avax\Facade\BaseFacade;
use Avax\HTTP\Session\Contracts\SessionBuilderInterface;
use Avax\HTTP\Session\Contracts\SessionManagerInterface;

/**
 * @method static SessionBuilderInterface for (string $namespace)
 * @method static SessionBuilderInterface builder()
 * @method static void put(string $key, mixed $value)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool has(string $key)
 * @method static void delete(string $key)
 * @method static void reset()
 *
 * @see SessionManagerInterface
 * @see SessionBuilderInterface
 */
final class Session extends BaseFacade
{
    protected static string $accessor = SessionManagerInterface::class;
}
