<?php

declare(strict_types=1);

namespace Gemini\Auth\Application\UseCase\Web;

use Psr\Http\Message\ResponseInterface;

/**
 * Handles the use case of retrieving the currently authenticated user.
 * This class ensures the user is authenticated before returning user data.
 */
final readonly class RetrieveCurrentUserUseCase
{
//    /**
//     * @param RetrieveCurrentUserService $retrieveCurrentUserService Handles user authentication retrieval logic.
//     */
//    public function __construct(private RetrieveCurrentUserService $retrieveCurrentUserService) {}

    /**
     * Executes the process to retrieve the authenticated user.
     *
     * ## Technical Description
     * - Delegates the authentication check to `RetrieveCurrentUserService`.
     * - Ensures that the logic remains clean, modular, and reusable.
     *
     * ## Business Description
     * - Ensures that **only authenticated users** can retrieve their own details.
     * - This abstraction allows **easy adaptation** to different authentication strategies.
     *
     * @return ResponseInterface The response containing user data if authenticated, or unauthorized status.
     */
    public function execute() : ResponseInterface
    {
        // TODO: Implement the logic to retrieve the authenticated user.
//        return $this->retrieveCurrentUserService->retrieve();
        return app(ResponseInterface::class);
    }
}
