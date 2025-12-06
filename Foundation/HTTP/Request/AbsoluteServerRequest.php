<?php
/** @noinspection ALL */

declare(strict_types=1);

namespace Avax\HTTP\Request;

use Avax\HTTP\Response\Classes\Stream;
use Avax\HTTP\URI\UriBuilder;
use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Http\Message\UriInterface;
use RuntimeException;

/**
 * Class AbsoluteServerRequest
 *
 * Base class implementing PSR-7's ServerRequestInterface.
 * Integrates ParameterBag for easy handling of parameters and manages uploaded files.
 */
class AbsoluteServerRequest implements ServerRequestInterface
{
    /**
     * @var array $attributes
     *
     * Stores attributes for an entity. This array might be used to dynamically
     * add or remove attributes without cluttering the class properties.
     */
    protected array $attributes = [];

    /**
     * @var array $headers
     * An array to store HTTP headers for the request. This is initialized as an empty
     * array and will be populated later based on specific headers required for the request.
     */
    protected array $headers = [];

    /**
     * Represents the HTTP method of the request (e.g., GET, POST).
     *
     * This is initialized from the `REQUEST_METHOD` server parameter.
     */
    protected string $method;

    /**
     * @var UriInterface $uri Represents the Uniform Resource Identifier (URI) for the resource.
     *                        Used consistently across functions to refer the endpoint being accessed or manipulated.
     *                        This variable may be set and modified frequently within different contexts, so it is
     *                        essential to maintain clarity about its purpose and usage to avoid confusion or misuse.
     */
    protected UriInterface $uri;

    /**
     * Handles the processing and validation of the request body.
     *
     * @param array $body The associative array representing the request body.
     *
     * This function processes the incoming request body and ensures that all necessary fields are present and valid.
     * Important business logic constraints are enforced here to maintain data integrity.
     */
    protected StreamInterface $body;

    /**
     * The version of the protocol being used.
     * This value may dictate how the server and client handle certain HTTP features.
     */
    protected string $protocolVersion = '1.1';

    /**
     * @var array $serverParams
     *
     * Holds server-specific parameters. This array is initialized as empty and expected to be populated
     * with parameters relevant to the server environment. It is crucial for accessing server configurations
     * and details required for various operations.
     *
     * The rationale for using an array here is to keep a structured, key-value format for easy retrieval
     * and manipulation of server parameters as needed throughout the codebase.
     */
    protected array $serverParams = [];

    /**
     * Class representing a database query builder.
     *
     * This class encapsulates logic for constructing SQL queries dynamically.
     * The primary goal is to provide an interface for developers to build complex
     * SQL queries using a fluent API, making it easier to maintain and read.
     *
     * The class uses a combination of SQL fragments and placeholders to
     * securely build queries and prevent SQL injection.
     */
    protected ParameterBag $query;

    /**
     * Class handling HTTP requests.
     *
     * This class encapsulates all incoming HTTP request data and provides methods
     * to interact with that data, ensuring uniform handling of different types
     * of requests (GET, POST, etc.). It abstracts the complexities of dealing with raw
     * input data and provides a structured way to access them.
     */
    protected ParameterBag $request;

    /**
     * Class holds methods to handle HTTP cookies.
     *
     * @class CookiesHelper
     * Final class to prevent inheritance.
     * Provides methods for setting, getting, and deleting cookies.
     */
    protected ParameterBag $cookies;

    /**
     * Constructor to initialize server request object.
     *
     * @param array|null           $server        Server parameters or defaults to $_SERVER.
     * @param UriInterface|null    $uri           URI of the request.
     * @param StreamInterface|null $body          Body of the request.
     * @param array|null           $queryParams   Query parameters or defaults to $_GET.
     * @param array|null           $parsedBody    Parsed body or an empty array.
     * @param array|null           $cookies       Cookie parameters or defaults to $_COOKIE.
     * @param array                $uploadedFiles Uploaded files parsed from the request.
     */
    public function __construct(
        array|null           $server = null,
        UriInterface|null    $uri = null,
        StreamInterface|null $body = null,
        array|null           $queryParams = null,
        array|null           $parsedBody = null,
        array|null           $cookies = null,
        protected array      $uploadedFiles = [],
    ) {
        $this->serverParams = $server ?? $_SERVER;
        $this->uri          = $uri ?? $this->initializeUri(requestUri: $this->serverParams['REQUEST_URI'] ?? '/');
        $resource           = fopen('php://temp', 'r+');
        if ($resource === false) {
            throw new RuntimeException(message: 'Unable to create temporary stream for request body.');
        }

        $this->body            = $body ?? new Stream(stream: $resource);
        $this->method          = $this->serverParams['REQUEST_METHOD'] ?? 'GET';
        $this->protocolVersion = $this->serverParams['SERVER_PROTOCOL'] ?? '1.1';
        $this->query           = new ParameterBag(parameters: $queryParams ?? $_GET);
        $this->request         = new ParameterBag(parameters: is_array($parsedBody) ? $parsedBody : []);
        $this->cookies         = new ParameterBag(parameters: $cookies ?? $_COOKIE);
        $this->headers         = $this->extractHeaders(server: $this->serverParams);
    }

    private function initializeUri(string $requestUri) : UriInterface
    {
        return UriBuilder::createFromString(uri: $requestUri);
    }

    /**
     * Extract HTTP headers from server parameters.
     *
     * @param array $server Server parameters.
     *
     * @return array Extracted headers.
     */
    private function extractHeaders(array $server) : array
    {
        $headers = [];
        foreach ($server as $key => $value) {
            if (str_starts_with($key, 'HTTP_')) {
                $name           = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$name] = $value;
            }
        }

        return $headers;
    }

    /** ***PSR-7 Protocol Version Methods*** */


    /**
     * Retrieve the network protocol version used.
     *
     * @return string The current protocol version.
     */
    public function getProtocolVersion() : string
    {
        return $this->protocolVersion;
    }

    /**
     * Clone the instance with a new protocol version.
     *
     * @param string $version New protocol version.
     *
     * @return static Cloned instance with updated protocol version.
     */
    public function withProtocolVersion(string $version) : static
    {
        $clone                  = clone $this;
        $clone->protocolVersion = $version;

        return $clone;
    }

    /** ***Server Params Methods*** */

    /**
     * Retrieve server parameters.
     *
     * This method is used to fetch server parameters which may include details
     * such as server name, IP address, and other configurations. Understanding
     * the server environment is crucial for various functionalities like
     * logging, request handling, and more.
     *
     * Ensure that the server parameters are properly initialized before calling
     * this method to avoid inconsistencies in server-related operations.
     *
     * @return array Array containing server parameters.
     */
    public function getServerParams() : array
    {
        return $this->serverParams;
    }

    /**
     * Clone the instance with a new attribute.
     *
     * @param string $name  Name of the attribute to add or update.
     * @param mixed  $value Value to associate with the attribute name.
     *
     * @return static Cloned instance with the updated attribute.
     *
     * Intent: This method allows for immutability by cloning the current
     * instance and then modifying the clone, preserving the original instance.
     */
    public function withAttribute(string $name, mixed $value) : static
    {
        $clone                    = clone $this;
        $clone->attributes[$name] = $value;

        return $clone;
    }

    /**
     * Clone the instance without a specified attribute.
     *
     * The rationale behind cloning the instance is to adhere to the immutability principle, ensuring
     * the original instance remains unchanged and any modifications are reflected in a new instance.
     * This can be particularly useful in scenarios where objects need to be shared across different
     * parts of an application without the risk of unintended side-effects.
     *
     * @param string $name Attribute name to be removed from the cloned instance.
     *
     * @return static Cloned instance without the specified attribute.
     */
    public function withoutAttribute(string $name) : static
    {
        $clone = clone $this;
        unset($clone->attributes[$name]);

        return $clone;
    }

    /**
     * Retrieve the attributes for the current instance.
     *
     * @return array The set of attributes stored in the instance.
     */
    public function getAttributes() : array
    {
        return $this->attributes;
    }

    /**
     * Retrieve all query parameters as an associative array.
     *
     * @return array Associative array of all query parameters.
     */
    public function getQueryParams() : array
    {
        return $this->query->all();
    }

    /** ***Query and Request Parameter Methods*** */

    /**
     * Clone the instance with new query parameters.
     *
     * @param array $query New query parameters.
     *
     * @return static Cloned instance with updated query parameters.
     */
    public function withQueryParams(array $query) : static
    {
        $clone        = clone $this;
        $clone->query = new ParameterBag(parameters: $query);

        return $clone;
    }

    /**
     * Retrieve and parse the body of the HTTP request.
     *
     * This method leverages the Foundation's ability to retrieve all input data from the request.
     * It returns the parsed body content as an associative array.
     *
     * @return array Parsed contents of the HTTP request body.
     */
    public function getParsedBody() : array
    {
        return $this->request->all();
    }

    /**
     * Clone the instance with a new parsed body.
     *
     * @param mixed $data Parsed body data (must be an array).
     *
     * @return static Cloned instance with updated parsed body.
     */
    public function withParsedBody(mixed $data) : static
    {
        $clone          = clone $this;
        $clone->request = new ParameterBag(parameters: is_array($data) ? $data : []);

        return $clone;
    }

    /**
     * Retrieve all cookie parameters from the cookies store.
     *
     * @return array An associative array of all cookie parameters.
     */
    public function getCookieParams() : array
    {
        return $this->cookies->all();
    }

    /** ***Cookie Parameter Methods*** */

    /**
     * Clone the instance with new cookie parameters.
     *
     * @param array $cookies New cookie parameters.
     *
     * @return static Cloned instance with updated cookie parameters.
     */
    public function withCookieParams(array $cookies) : static
    {
        $clone          = clone $this;
        $clone->cookies = new ParameterBag(parameters: $cookies);

        return $clone;
    }

    /**
     * Retrieve the list of uploaded files.
     *
     * @return array The array of uploaded files.
     */
    public function getUploadedFiles() : array
    {
        return $this->uploadedFiles;
    }

    /** ***Uploaded Files Methods*** */

    /**
     * Clone the instance with new uploaded files.
     *
     * @param array $uploadedFiles Array of UploadedFileInterface instances.
     *
     * @return static Cloned instance with updated uploaded files.
     * @throws InvalidArgumentException If any file does not implement UploadedFileInterface.
     */
    public function withUploadedFiles(array $uploadedFiles) : static
    {
        foreach ($uploadedFiles as $uploadedFile) {
            if (! $uploadedFile instanceof UploadedFileInterface) {
                throw new InvalidArgumentException(message: 'Uploaded files must implement UploadedFileInterface');
            }
        }

        $clone                = clone $this;
        $clone->uploadedFiles = $uploadedFiles;

        return $clone;
    }

    /**
     * Retrieve the request target.
     *
     * Returns the request target, which is the path of the URI.
     * If the query string is present, it appends it to the path.
     *
     * @return string The request target.
     */
    public function getRequestTarget() : string
    {
        $target = $this->uri->getPath();
        $query  = $this->uri->getQuery();

        return $query !== '' && $query !== '0' ? $target . "?" . $query : $target;
    }

    /** ***Request Target Methods*** */

    /**
     * Clone the instance with a new request target.
     *
     * @param string $requestTarget New request target.
     *
     * @return static Cloned instance with updated request target.
     */
    public function withRequestTarget(string $requestTarget) : static
    {
        $clone      = clone $this;
        $clone->uri = $clone->uri->withPath(path: $requestTarget);

        return $clone;
    }

    /**
     * Retrieve the header line for a given header name.
     *
     * Combines all the header values for the specified name into a single string
     * separated by commas.
     *
     * @param string $name The name of the header.
     *
     * @return string The header line as a string.
     */
    public function getHeaderLine(string $name) : string
    {
        return implode(', ', $this->getHeader(name: $name));
    }

    /** ***Header Methods*** */

    /**
     * Retrieve the values of a specified header.
     *
     * Returns an array of values for the specified header name. If the header does
     * not exist, an empty array is returned.
     *
     * @param string $name The name of the header.
     *
     * @return array An array of header values.
     */
    public function getHeader(string $name) : array
    {
        return $this->headers[$name] ?? [];
    }

    /**
     * Clone the instance with a new header.
     *
     * @param string $name  Header name.
     * @param mixed  $value Header value.
     *
     * @return static Cloned instance with updated header.
     */
    public function withHeader(string $name, mixed $value) : static
    {
        $clone                 = clone $this;
        $clone->headers[$name] = (array) $value;

        return $clone;
    }

    /**
     * Clone the instance with an added header.
     *
     * @param string $name  Header name.
     * @param mixed  $value Header value to add.
     *
     * @return static Cloned instance with added header.
     */
    public function withAddedHeader(string $name, mixed $value) : static
    {
        $clone                 = clone $this;
        $clone->headers[$name] = array_merge($this->getHeader(name: $name), (array) $value);

        return $clone;
    }

    /**
     * Clone the instance without a specified header.
     *
     * @param string $name Header name to remove.
     *
     * @return static Cloned instance without the header.
     */
    public function withoutHeader(string $name) : static
    {
        $clone = clone $this;
        unset($clone->headers[$name]);

        return $clone;
    }

    /**
     * Retrieve all headers.
     *
     * Returns an associative array of all headers, where the key is the header name
     * and the value is an array of header values.
     *
     * @return array An associative array of all headers.
     */
    public function getHeaders() : array
    {
        return $this->headers;
    }

    /**
     * Clone the instance with a new body.
     *
     * @param StreamInterface $stream New body body.
     *
     * @return static Cloned instance with updated body.
     */
    public function withBody(StreamInterface $stream) : static
    {
        $clone       = clone $this;
        $clone->body = $stream;

        return $clone;
    }

    /** ***Body Methods*** */

    /**
     * Retrieve the body of the request.
     *
     * Returns the body of the request as a StreamInterface instance.
     *
     * @return StreamInterface The body of the request.
     */
    public function getBody() : StreamInterface
    {
        return $this->body;
    }

    /**
     * Clone the instance with a new HTTP method.
     *
     * @param string $method HTTP method.
     *
     * @return static Cloned instance with updated method.
     */
    public function withMethod(string $method) : static
    {
        $clone         = clone $this;
        $clone->method = $method;

        return $clone;
    }

    /** ***Method Methods*** */

    /**
     * Retrieve the HTTP method of the request.
     *
     * Returns the HTTP method used for the request (e.g., GET, POST).
     *
     * @return string The HTTP method as a string.
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * Retrieve the URI of the request.
     *
     * Returns the URI of the request as a UriInterface instance.
     *
     * @return UriInterface The URI of the request.
     */
    public function getUri() : UriInterface
    {
        return $this->uri;
    }

    /** ***URI Methods*** */

    /**
     * Clone the instance with a new URI.
     *
     * @param UriInterface $uri          New URI.
     * @param bool         $preserveHost Whether to preserve the host header.
     *
     * @return static Cloned instance with updated URI.
     */
    public function withUri(UriInterface $uri, bool $preserveHost = false) : static
    {
        $clone      = clone $this;
        $clone->uri = $uri;
        if (! $preserveHost || ! $this->hasHeader(name: 'Host')) {
            $clone->headers['Host'] = [$uri->getHost()];
        }

        return $clone;
    }

    /**
     * Check if a given header exists.
     *
     * Determines whether a specified header is present in the request.
     *
     * @param string $name The name of the header.
     *
     * @return bool True if the header exists, false otherwise.
     */
    public function hasHeader(string $name) : bool
    {
        return isset($this->headers[$name]);
    }

    /** ***Header Presence Check*** */

    /**
     * Retrieve the client IP address.
     *
     * @return string|null The client IP address or null if not found.
     */
    public function getClientIp() : string|null
    {
        $keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($keys as $key) {
            if (! empty($this->serverParams[$key])) {
                $ipList = explode(',', (string) $this->serverParams[$key]);

                // Log ambiguous cases for debugging
                if (count($ipList) > 1) {
                    error_log(sprintf('Multiple IPs found in %s: %s', $key, implode(', ', $ipList)));
                }

                return trim(current($ipList));
            }
        }

        return null;
    }

    /**
     * Retrieve an attribute value by key, with an optional default.
     *
     * @param string $key     The key to look up in the attributes.
     * @param mixed  $default The default value to return if the key does not exist.
     *
     * @return mixed The value of the attribute or the default value.
     */
    public function route(string $key, mixed $default = null) : mixed
    {
        return $this->getAttribute($key, $default);
    }

    /**
     * Retrieve an attribute value by its name, or return a default value if the attribute is not found.
     *
     * The rationale for returning a default value is to provide a safe fallback mechanism, avoiding potential
     * null pointer exceptions or undefined index errors which might occur if the attribute does not exist.
     *
     * @param string $name    The name of the attribute to retrieve.
     * @param mixed  $default The default value to return if the attribute is not set. Defaults to null.
     *
     * @return mixed The value of the attribute if found, otherwise the default value.
     */
    public function getAttribute(string $name, mixed $default = null) : mixed
    {
        return $this->attributes[$name] ?? $default;
    }


}
