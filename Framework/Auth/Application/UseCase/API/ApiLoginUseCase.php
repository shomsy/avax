<?php

declare(strict_types=1);

namespace Gemini\Auth\Application\UseCase\API;

use Gemini\Auth\Contracts\Identity\Subject\UserInterface;
use Gemini\Auth\Domain\Exception\AuthenticationException;
use Gemini\Auth\Domain\ValueObject\Credentials;
use Gemini\Auth\DTO\AuthenticationDTO;
use Gemini\HTTP\Request\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ApiLoginUseCase
 *
 * This class encapsulates the logic for authenticating a user via an API.
 * The final modifier ensures that this class cannot be extended.
 */
final class ApiLoginUseCase
{
    /**
     * Key for accessing identifier in the request data. Could be email or username
     * Keeping it private and constant ensures consistency and immutability.
     */
    private const string IDENTIFIER_KEY = 'identifier';

    /**
     * Key for accessing password in the request data.
     * Similar to IDENTIFIER_KEY, it's immutable and consistent.
     */
    private const string PASSWORD_KEY = 'password';

    /**
     * Executes the login process.
     *
     * This method handles the main flow of user authentication including:
     * - Extracting credentials from the request.
     * - Authenticating the user.
     * - Generating a JWT token.
     * - Constructing a success or error response.
     *
     * @param Request $request The HTTP request containing login data.
     *
     * @return ResponseInterface The HTTP response with login results.
     * @throws \Exception This is a general exception thrown if anything unexpected happens.
     */
    public function execute(Request $request) : ResponseInterface
    {
        try {
            $credentials = $this->createCredentialsFromRequest(request: $request);
            $user        = auth()->login(credentials: $credentials);
            $token       = $this->generateJwtTokenForUser(user: $user, request: $request);

            return $this->successResponse(user: $user, token: $token);
        } catch (AuthenticationException $authenticationException) {
            return $this->errorResponse($authenticationException);
        }
    }

    /**
     * Extracts credentials from the HTTP request.
     *
     * This method parses the request data to create a Credentials object
     * using the identifier and password extracted from the request.
     *
     * @param Request $request The HTTP request containing login data.
     *
     * @return Credentials The created credentials object.
     * @throws \ReflectionException
     */
    private function createCredentialsFromRequest(Request $request) : Credentials
    {
        $authenticationData = [
            self::IDENTIFIER_KEY => $request->get(key: self::IDENTIFIER_KEY),
            self::PASSWORD_KEY   => $request->get(key: self::PASSWORD_KEY),
        ];

        return new Credentials(authenticationDTO: new AuthenticationDTO(data: $authenticationData));
    }

    /**
     * Generates a JWT token for the authenticated user.
     *
     * The token contains the user's id, email, and username.
     * This token will be used for further authenticated API requests.
     *
     * @param UserInterface $user    The authenticated user.
     * @param Request       $request The HTTP request.
     *
     * @return string The generated JWT token.
     */
    private function generateJwtTokenForUser(UserInterface $user, Request $request) : string
    {
        $payload = [
            'sub'      => $user->getId(),
            'email'    => $user->getEmail(),
            'username' => $user->getUsername(),
        ];

        return $request->generateJwtToken(payload: $payload);
    }

    /**
     * Constructs a successful response.
     *
     * This method formats a successful login response which includes
     * the generated JWT token and user details.
     *
     * @param UserInterface $user  The authenticated user.
     * @param string        $token The generated JWT token.
     *
     * @return ResponseInterface A response indicating successful authentication.
     * @throws \Exception If the response construction fails.
     */
    private function successResponse(UserInterface $user, string $token) : ResponseInterface
    {
        return response()->send(
            data: [
                      'status' => 'success',
                      'token'  => $token,
                      'user'   => [
                          'id'       => $user->getId(),
                          'email'    => $user->getEmail(),
                          'username' => $user->getUsername(),
                      ],
                  ],
        );
    }

    /**
     * Constructs an error response.
     *
     * This method formats an error response, indicating invalid credentials.
     *
     * @param AuthenticationException $authenticationException The exception thrown during authentication failure.
     *
     * @return ResponseInterface A response indicating failed authentication.
     * @throws \Exception If the response construction fails.
     */
    private function errorResponse(AuthenticationException $authenticationException) : ResponseInterface
    {
        return response()->send(
            data  : [
                        'status'  => 'error',
                        'message' => 'Invalid credentials. ' . $authenticationException->getMessage(),
                    ],
            status: 401,
        );
    }
}
