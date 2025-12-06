namespace Avax\Auth\Contracts;

use Avax\Auth\Contracts\Identity\Subject\UserInterface;
use Avax\Auth\Data\Credentials;

/**
 * Interface AuthInterface
 *
 * Defines the contract for authentication-related operations.
 * This interface standardizes methods for user login, logout, and session retrieval.
 */
interface AuthInterface
{
    /**
     * Authenticate and log in a user with the given credentials.
     *
     * @param Credentials $credentials AccessControl credentials.
     * @return UserInterface The authenticated user instance.
     */
    public function login(Credentials $credentials): UserInterface;

    /**
     * Log out the currently authenticated user.
     */
    public function logout(): void;

    /**
     * Get the currently authenticated user.
     *
     * @return UserInterface|null
     */
    public function user(): ?UserInterface;

    /**
     * Check if the user is authenticated.
     *
     * @return bool
     */
    public function check(): bool;
}
