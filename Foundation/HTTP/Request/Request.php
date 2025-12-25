<?php
/** @noinspection GlobalVariableUsageInspection */

declare(strict_types=1);

namespace Avax\HTTP\Request;

use Avax\HTTP\Request\Traits\InputManagementTrait;
use Avax\HTTP\Request\Traits\JwtTrait;
use Avax\HTTP\Request\Traits\SessionManagementTrait;
use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\Session\Contracts\SessionInterface;
use Avax\HTTP\Session\NullSession;
use Avax\HTTP\URI\UriBuilder;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;
use Throwable;

/**
 * Class Request
 *
 * This class extends `AbsoluteServerRequest` to handle HTTP requests and add custom functionality like session
 * management, input handling, and JWT integration.
 * It supports instantiation using global PHP variables, making it
 * suitable for working with web applications in a standardized way.
 *
 * - The class uses Laravel-style session access to simplify working with session.
 * - Provides utility for creating custom URIs from global variables ($_SERVER).
 * - Includes functionalities that can be extended via multiple reusable traits (InputManagementTrait, JwtTrait, etc.).
 *
 * The class enforces strict typing to align with modern PHP practices.
 */
class Request extends AbsoluteServerRequest implements ServerRequestInterface
{
    use InputManagementTrait;
    use SessionManagementTrait;
    use JwtTrait;

    /**
     * The session instance for the request, defaults to a NullSession if no session is provided.
     */
    protected SessionInterface $session;

    /**
     * Stores the files uploaded with the request.
     */
    protected ParameterBag $files;

    /**
     * Holds parsed JSON parameters for this request.
     */
    private ParameterBag $json;

    /**
     * Constructor to initialize the request with various parameters.
     *
     * @param SessionInterface|string|null $session       The session instance or its string equivalent, defaults to
     *                                                    NullSession.
     * @param array                        $serverParams  The server parameters, typically from $_SERVER.
     * @param UriInterface|string|null     $uri           The request URI, provided as a UriInterface or string.
     * @param Stream|string|null           $body          The body of the request as a stream or string.
     * @param array                        $queryParams   An array of query parameters, typically from $_GET.
     * @param array                        $parsedBody    Parsed request body, typically from POST data.
     * @param array                        $cookies       An array of cookies, typically from $_COOKIE.
     * @param array                        $uploadedFiles An array of uploaded files, typically from $_FILES.
     *
     * @return void
     */
    #[\Override]
    public function __construct(
        SessionInterface|string|null $session = null,
        array                        $serverParams = [],
        UriInterface|string|null     $uri = null,
        Stream|string|null           $body = null,
        array                        $queryParams = [],
        array                        $parsedBody = [],
        array                        $cookies = [],
        array                        $uploadedFiles = []
    ) {
        parent::__construct(
            server       : $serverParams,
            uri          : $uri,
            body         : $body,
            queryParams  : $queryParams,
            parsedBody   : $parsedBody,
            cookies      : $cookies,
            uploadedFiles: $uploadedFiles
        );

        // Default to NullSession to avoid null-checks for session management.
        $this->session = $session ?? new NullSession();


        // Wrap uploaded files into a ParameterBag for easier management and access.
        $this->files = new ParameterBag(parameters: $uploadedFiles);

        // Initialize an empty JSON ParameterBag for parsing and handling JSON bodies.
        $this->json = new ParameterBag(parameters: $this->parseJsonBody());
    }

    /**
     * Creates a Request instance from global PHP variables.
     *
     * This is especially useful for HTTP server handling where $_SERVER, $_GET, $_POST, $_COOKIE, etc.,
     * need to be converted into a request object.
     *
     * @throws RuntimeException If creation fails due to unexpected global data.
     */
    public static function createFromGlobals() : self
    {
        try {
            // Build the URI from the global variables ($_SERVER in this case).
            $uri = self::buildUriFromGlobals();

            return new self(
                session      : app(abstract: SessionInterface::class), // Retrieve session from the IoC container.
                serverParams : $_SERVER,
                uri          : $uri,
                body         : new Stream(stream: fopen(filename: 'php://input', mode: 'rb')),
                queryParams  : $_GET,
                parsedBody   : $_POST,
                cookies      : $_COOKIE,
                uploadedFiles: $_FILES
            );
        } catch (Throwable $throwable) {
            // Catch unexpected exceptions during construction and wrap them in a runtime exception.
            throw new RuntimeException(
                message: "Failed to create Request from globals.", code: 0, previous: $throwable
            );
        }
    }

    /**
     * Builds a URI from the global server data ($_SERVER).
     *
     * The resulting URI includes the scheme (HTTP/HTTPS), host, port (if non-standard), and path along
     * with the query string. The method ensures compatibility across different server configurations.
     *
     * @return UriInterface The constructed URI object.
     */
    protected static function buildUriFromGlobals() : UriInterface
    {
        // Determine the request scheme. Default to HTTP unless HTTPS is explicitly enabled in the server environment.
        $scheme = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

        // Resolve the host using HTTP_HOST, SERVER_NAME, or a localhost fallback.
        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

        // Non-standard ports are appended to the base URI.
        $port = $_SERVER['SERVER_PORT'] ?? null;

        $baseUri = sprintf('%s://%s', $scheme, $host);
        if ($port && $port !== 80 && $port !== 443) {
            $baseUri .= ':' . $port;
        }

        // Parse the request URI path and query components.
        $path  = parse_url(url: $_SERVER['REQUEST_URI'] ?? '/', component: PHP_URL_PATH) ?? '/';
        $query = $_SERVER['QUERY_STRING'] ?? '';

        // Construct and return a usable URI object.
        return UriBuilder::fromBaseUri(baseUri: $baseUri)
            ->withPath(path: $path)
            ->withQuery(query: $query);
    }

    /**
     * Returns the request URI path.
     *
     * Example: If the full URI is "https://example.com/path?query=1", this method will return "/path".
     */
    public function path() : string
    {
        return $this->getUri()->getPath();
    }

    /**
     * Provides Laravel-style access to session.
     *
     * This allows you to retrieve session data, or the session object itself when no key is provided.
     *
     * @param string|null $key     The key to retrieve from the session.
     * @param mixed       $default Default value if the key does not exist in the session.
     *
     * @return mixed The value associated with the key, or the session object itself if no key is provided.
     */
    public function session(string|null $key = null, mixed $default = null) : mixed
    {
        $this->ensureSession(); // Ensure session instance is valid.

        // Return entire session instance if no key is provided, otherwise fetch the requested key.
        return $key === null
            ? $this->session
            : $this->session->get(key: $key, default: $default);
    }

    /**
     * Ensures that a valid SessionInterface instance is available.
     *
     * This is mainly used as a fallback to lazily resolve the session from the dependency container
     * in case it hasn't been explicitly set during initialization.
     */
    protected function ensureSession() : void
    {
        if (! isset($this->session) || $this->session instanceof NullSession) {
            $this->session = app(abstract: SessionInterface::class);
        }
    }

    /**
     * Sets a new session instance explicitly.
     *
     * @param SessionInterface $session The session instance to set.
     */
    public function setSession(SessionInterface $session) : void
    {
        $this->session = $session;
    }

    /**
     * Checks if a given key exists in the session.
     */
    public function hasSession(string $key) : bool
    {
        return $this->session->has(key: $key);
    }

    /**
     * Writes a value to the session.
     */
    public function putSession(string $key, mixed $value) : void
    {
        $this->session->set(key: $key, value: $value);
    }

    /**
     * Removes a key from the session.
     */
    public function forgetSession(string $key) : void
    {
        $this->session->remove(key: $key);
    }

    /**
     * Retrieves the user information, either from the session or from another source if not available.
     *
     * @return mixed The user data retrieved.
     */
    public function user() : mixed
    {
        return $this->session->get('user'); // TODO: if not in session, then from JWT !!!
    }

}