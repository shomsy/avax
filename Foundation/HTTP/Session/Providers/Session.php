<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Providers;

use Avax\Support\Facades\Facade;

/**
 * Session - Laravel-Style Session Facade
 *
 * Static proxy to SessionProvider instance for convenient access.
 *
 * Provider-Consumer Pattern:
 * - Session facade provides static access
 * - SessionProvider is the underlying provider
 * - SessionConsumer handles contextual operations
 *
 * @example
 *   Session::put('user_id', 123);
 *   Session::for('cart')->secure()->put('items', $items);
 *   Session::flash()->success('Saved!');
 *
 * @method static void put(string $key, mixed $value, ?int $ttl = null)
 * @method static mixed get(string $key, mixed $default = null)
 * @method static bool has(string $key)
 * @method static void forget(string $key)
 * @method static array all()
 * @method static void flush()
 * @method static SessionConsumer for (string $context)
 * @method static SessionConsumer scope(string $namespace)
 * @method static Flash flash()
 * @method static Events events()
 * @method static self registerPolicy(Policies\PolicyInterface $policy)
 * @method static self enableAudit(?string $path = null)
 * @method static void snapshot(string $name)
 * @method static void restore(string $name)
 * @method static mixed remember(string $key, callable $callback, ?int $ttl = null)
 *
 * @package Avax\HTTP\Session
 * @see     SessionProvider
 */
class Session extends Facade
{
    /**
     * Get the accessor for the facade.
     *
     * @return string The facade accessor name.
     */
    protected static function getFacadeAccessor() : string
    {
        return SessionProvider::class;
    }
}
