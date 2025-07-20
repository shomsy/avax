<?php

declare(strict_types=1);

namespace Gemini\Auth\Interface\HTTP\Middleware;

use Closure;
use Gemini\Auth\Application\Service\RABC\AccessControlService;
use Gemini\Auth\Contracts\Identity\Subject\UserInterface;
use Gemini\HTTP\Request\Request;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Middleware for handling user authorization based on policies.
 *
 * This middleware ensures that a user has access to a specific route or functionality
 * by verifying the defined authorization policy and the user's associated permissions or roles.
 */
final readonly class AuthorizeMiddleware
{
    /**
     * @var AccessControlService $authorization The service responsible for handling
     *                                          access control decisions, such as checking user roles and permissions.
     */
    public function __construct(private AccessControlService $authorization) {}

    /**
     * Handles the authorization process for incoming requests.
     *
     * This method is executed during the middleware lifecycle to ensure that the user
     * is authorized to proceed based on a specified policy.
     *
     * Steps:
     * - Retrieve the authorization policy from the request.
     * - Validate the request's user object against the defined policy.
     * - If the policy is not met, an exception is thrown.
     * - Otherwise, the request is passed to the next middleware.
     *
     * @param Request $request The HTTP request containing user and policy attributes.
     * @param Closure $next    The next middleware or final request handler to execute.
     *
     * @return ResponseInterface The processed response if authorization passes.
     * @throws RuntimeException If the policy is missing, the user is not found, or authorization fails.
     *
     */
    public function handle(Request $request, Closure $next) : ResponseInterface
    {
        // Retrieve the authorization policy from the request attributes.
        $policy = $request->getAttribute(name: 'route:authorization');

        // Ensure the policy exists and is a valid string.
        if (! is_string($policy)) {
            throw new RuntimeException(message: 'Route authorization policy is missing or invalid.');
        }

        // Retrieve the user from the request attributes.
        $user = $request->getAttribute(name: 'user');

        // Ensure the user is available and implements the UserInterface contract.
        if (! $user instanceof UserInterface) {
            throw new RuntimeException(message: 'Cannot authorize: No user available in request.');
        }

        // Verify if the user is authorized for the specific policy.
        if (! $this->authorization->check(user: $user, policy: $policy)) {
            throw new RuntimeException(message: "Unauthorized: policy [{$policy}] denied.");
        }

        // If authorization succeeded, pass the request to the next middleware.
        return $next($request);
    }
}