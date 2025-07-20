<?php

declare(strict_types=1);

namespace Gemini\Auth\Application\UseCases\Web;

use Exception;
use Gemini\Auth\Application\Service\LogoutAction;
use Psr\Http\Message\ResponseInterface;

/**
 * The `LogoutUseCase` class encapsulates the user logout process.
 *
 * Responsibilities:
 * - Delegates logout logic to `LogoutAction`, ensuring separation of concerns.
 * - Makes logout logic reusable across different authentication contexts.
 * - Isolates business logic from lower-level session and security management.
 */
final readonly class LogoutUseCase
{
    /**
     * LogoutUseCase constructor.
     *
     * @param LogoutAction $logoutService Handles logout logic.
     */
    public function __construct(private LogoutAction $logoutService) {}

    /**
     * Executes the complete logout process.
     *
     * @return ResponseInterface Response object indicating successful logout, with a new CSRF token.
     * @throws Exception
     */
    public function execute() : ResponseInterface
    {
        return $this->logoutService->logout();
    }
}
