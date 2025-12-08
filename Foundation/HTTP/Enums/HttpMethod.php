<?php

declare(strict_types=1);

namespace Avax\HTTP\Enums;

/**
 * Enum HttpMethod
 *
 * Represents all possible HTTP methods in a web application.
 * Utilizing an enum class ensures all HTTP methods are handled uniformly
 * and reduces the likelihood of typos or unsupported methods being used.
 */
enum HttpMethod: string
{
    /**
     * HTTP method GET.
     *
     * This constant represents the HTTP GET method used to request data from a specified resource.
     * It's part of HTTP/1.1, RFC 2616, and is widely utilized for retrieving information without
     * modifying the state of the resource.
     */
    case GET = 'GET';

    /**
     * Represents an HTTP POST request.
     *
     * This enum value signifies that the request method is POST, typically used
     * to submit data to be processed to a specified resource.
     */
    case POST = 'POST';

    /**
     * Indicates the HTTP PUT method, typically used for updating resources on a server.
     * Used to specify the idempotence and significance of the HTTP method in API operations.
     */
    case PUT = 'PUT';

    /**
     * HTTP DELETE method used to delete a specified resource.
     *
     * This constant represents the HTTP DELETE request. It's typically used in RESTful APIs
     * to signal that a resource identified by a URI should be deleted.
     *
     * Choosing DELETE over other HTTP methods is following RESTful principles, ensuring
     * that the method semantics are clear and standardized.
     */
    case DELETE = 'DELETE';

    /**
     * Represents an HTTP HEAD request method.
     *
     * The HEAD method is used to retrieve the headers that are returned if the specified resource would be requested
     * with an HTTP GET method. As such, it serves a similar purpose as GET but without the response body, making it
     * useful for checking what a GET request will return before actually making the request.
     */
    case HEAD = 'HEAD';

    /**
     * HTTP method constant representing the 'CONNECT' request method.
     *
     * The 'CONNECT' method starts two-way communications with the requested resource,
     * typically with the use-case of establishing a tunnel to the server identified by the target resource.
     * This is commonly used for SSL tunneling through an HTTP proxy.
     */
    case CONNECT = 'CONNECT';

    /**
     * Enumeration of HTTP request methods.
     * Options can be used in CORS pre-flight requests.
     */
    case OPTIONS = 'OPTIONS';

    /**
     * Enum value representing the HTTP TRACE method.
     * TRACE is typically used for diagnostic purposes. It echoes back the received request
     * so that a client can see what (if any) changes or additions have been made by intermediate servers.
     */
    case TRACE = 'TRACE';

    /**
     * HTTP PATCH method, used to apply partial modifications to a resource.
     *
     * @constant PATCH
     */
    case PATCH = 'PATCH';

    /**
     * Checks if the provided method is supported.
     *
     * @param string $method The HTTP method to check.
     *
     * @return bool True if the method is supported, otherwise false.
     *
     * Rationale: Using a match expression ensures that the method
     * comparison is concise and clear, making it easy to read and maintain.
     */
    public static function isSupported(string $method) : bool
    {
        return match ($method) {
            self::GET->value,
            self::POST->value,
            self::PUT->value,
            self::DELETE->value,
            self::HEAD->value,
            self::CONNECT->value,
            self::OPTIONS->value,
            self::TRACE->value,
            self::PATCH->value => true,
            default            => false,
        };
    }

    /**
     * Returns a list of all supported HTTP methods.
     *
     * @return array List of supported HTTP methods.
     *
     * Rationale: This method provides a comprehensive list of all supported methods,
     * making it easier to iterate over or validate against all possible HTTP methods
     * without hardcoding the values elsewhere.
     */
    public static function getSupportedMethods() : array
    {
        return array_map(static fn($case) => $case->value, self::cases());
    }
}
