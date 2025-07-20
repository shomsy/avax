<?php

declare(strict_types=1);

namespace Gemini\Auth\Application\UseCase\Web;

use Gemini\Auth\Application\Action\LoginAction;
use Gemini\Auth\Domain\Exception\AuthenticationException;
use Gemini\HTTP\Request\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * LoginUseCase orchestrates the login process by utilizing LoginAction.
 *
 * - It acts as an application-level use case, making it easy to adapt and extend.
 * - It ensures that login logic remains separate from controllers.
 */
final readonly class LoginUseCase
{
    /**
     * Constructor for injecting dependencies.
     *
     * @param \Gemini\Auth\Application\Action\LoginAction $loginAction
     */
    public function __construct(private LoginAction $loginAction) {}

    /**
     * Handles user login.
     *
     * @param Request $request The HTTP request containing login credentials.
     *
     * @return ResponseInterface The response indicating login success or failure.
     * @throws AuthenticationException
     * @throws \ReflectionException
     */
    public function execute(Request $request) : ResponseInterface
    {
        return $this->loginAction->login(request: $request);
    }

}
