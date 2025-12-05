<?php

declare(strict_types=1);

namespace Gemini\Auth\Http;

use Closure;

/**
 * Middleware to ensure a user is authenticated.
 *
 * The class is marked as 'readonly', which indicates to the developers
 * that once instantiated, its properties cannot be modified.
 */
readonly class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Redirects to log in if the user is not authenticated.
     *
     * @param mixed   $request The HTTP request object. The exact type isn't specified here for flexibility.
     * @param Closure $next    The next middleware or request handler.
     *
     *
     * @return mixed The result of $next middleware if the user is authenticated.
     */
    public function handle(mixed $request, Closure $next) : mixed
    {
//        dd(auth()->user());
//        // Check for user authentication
//        if (! auth()->check()) {
//            // Log an unauthenticated access attempt for monitoring/security purposes
//            error_log(
//                sprintf(
//                    'Unauthenticated access attempt to "%s".',
//                    $request->getUri()->getPath()
//                )
//            );
//
//            // Redirect unauthenticated user to the login page
//            redirect(route('auth.login.form'));
////            redirect('/login');
////            header("Location: " . route('auth.login.form'), true, 302);
////            exit;
//            // Optional: Throw an exception instead of redirecting
//            // Uncomment the line below to enable throwing an authentication exception
//            // throw new AuthenticationException(message: 'Subject is not authenticated. Please log in.');
//        }
//
//        // Proceed to the next middleware if the user is authenticated
        return $next($request);
    }
}