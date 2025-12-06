<?php

declare(strict_types=1);

namespace Avax\HTTP\Session;

use Avax\Facade\BaseFacade;
use Avax\HTTP\Session\API\FluentSession;
use Avax\HTTP\Session\API\SessionManager;

/**
 * Session Facade
 *
 * Provides static access to the SessionManager.
 *
 * Usage:
 *   Session::scope('cart')->store('items', [1, 2, 3]);
 *   Session::flash('success', 'Saved!');
 *   Session::remember('user', fn() => User::find($id));
 *
 * @method static SessionManager start()
 * @method static FluentSession scope(string $namespace)
 * @method static FluentSession in(string $namespace)
 * @method static FluentSession temporary(int $seconds)
 * @method static FluentSession builder()
 * @method static void flash(string $key, mixed $value, string $type = 'info')
 * @method static mixed remember(string $key, \Closure $callback)
 * @method static void put(string $key, mixed $value)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool has(string $key)
 * @method static void delete(string $key)
 * @method static array all()
 * @method static void invalidate()
 * @method static void regenerate(bool $deleteOldSession = true)
 * @method static string id()
 *
 * @see SessionManager
 */
final class Session extends BaseFacade
{
    protected static string $accessor = SessionManager::class;
}
