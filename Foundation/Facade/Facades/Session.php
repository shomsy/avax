<?php

declare(strict_types=1);

namespace Avax\Facade;

/**
 * 🧩 Session Facade
 * ------------------------------------------------------------
 * Provides a static interface to the Session Component.
 *
 * This allows you to use expressive and framework-friendly
 * calls like `Session::put()`, `Session::get()`, or `Session::flush()`
 * anywhere in your application — without manually resolving
 * the instance from the container.
 *
 * The facade resolves the concrete `SessionInterface` instance
 * from the dependency injector at runtime.
 *
 * 💡 It remains fully DI-compliant, since all calls are delegated
 * to the resolved instance rather than static state.
 *
 * @example
 *   Session::put('user_id', 42);
 *   $id = Session::get('user_id');
 *   Session::flush();
 *
 * @package Avax\Facade
 */
final class Session extends BaseFacade
{
    /**
     * The container key used to resolve the underlying session instance.
     *
     * @var string
     */
    protected static string $accessor = \Avax\HTTP\Session\Session::class;
}
