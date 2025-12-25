<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\Traits;

use Avax\DataHandling\ArrayHandling\Arrhae;
use Avax\HTTP\Request\ParameterBag;
use Avax\HTTP\Request\Request;
use JsonException;
use RuntimeException;

/**
 * Trait InputManagementTrait
 *
 * Provides methods for managing and accessing different types of inputs in a request.
 */
trait InputManagementTrait
{
    /**
     * Builds a query to fetch user data. This query is designed to be reusable for various components
     * that require user information, ensuring consistency across different parts of the application.
     */
    protected ParameterBag $query;

    /**
     * Processes the incoming request and determines the appropriate action.
     *
     * This function handles the initial entry point for requests. It parses incoming data,
     * performs necessary authentication and validation, and routes to the corresponding
     * business logic handlers.
     *
     * @param Request $request The incoming request object containing all the request data.
     *
     *
     * Key considerations:
     *  - Ensure data is sanitized to prevent security vulnerabilities.
     *  - Authentication and validation need to be done before any business logic is processed.
     *  - Handle edge cases such as missing parameters or invalid data formats gracefully.
     */
    protected ParameterBag $request;

    /**
     * Manages handling and storing cookies with additional functionality beyond standard methods.
     * Provides utility functions to set, get, and delete cookies while ensuring certain business rules and constraints.
     */
    protected ParameterBag $cookies;

    /**
     * Class FileProcessor
     *
     * The main purpose of this class is to handle the processing of files.
     * This implementation assumes that the files are processed in batches,
     * and hence utilizes a batch size parameter to control the amount of
     * processing done at a time.
     */
    protected ParameterBag $files;

    /**
     * Class OrderProcessor
     *
     * This class handles the processing of orders. It validates the order data,
     * applies discounts, and updates the inventory. It's designed to be instantiated
     * with dependency injection for better testability and decoupling.
     */
    private ParameterBag $json;

    /**
     * Retrieve all inputs from various sources and merge them into a single array.
     *
     * @return array An array containing all inputs.
     */
    public function allInputs() : array
    {
        return array_merge(
            $this->query->all(),
            $this->request->all(),
            $this->cookies->all(),
            isset($this->files) ? $this->files->all() : [],
            isset($this->json) ? $this->json->all() : [],
        );
    }

    /**
     * Retrieve a query parameter by key with an optional default.
     *
     * @param string $key     The key of the query parameter.
     * @param mixed  $default Optional default value.
     *
     * @return mixed The value of the query parameter or default.
     */
    public function query(string $key, mixed $default = null) : mixed
    {
        return $this->query->get(key: $key, default: $default);
    }

    /**
     * Retrieve an input, prioritizing query parameters, then request parameters.
     *
     * @param string $key     The key of the input.
     * @param mixed  $default Optional default value.
     *
     * @return mixed The value of the input or default.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->input(key: $key, default: $default);
    }

    /**
     * Retrieve an input, prioritizing query parameters over request parameters.
     *
     * @param string $key     The key of the input.
     * @param mixed  $default Optional default value.
     *
     * @return mixed The value of the input or default.
     */
    public function input(string $key, mixed $default = null) : mixed
    {
        if ($this->query->has(key: $key)) {
            return $this->query->get(key: $key);
        }

        return $this->request->has(key: $key) ? $this->request->get(key: $key) : $default;
    }

    /**
     * Check if an input exists in either query or request parameters.
     *
     * @param string $key The key of the input.
     *
     * @return bool True if the input exists, false otherwise.
     */
    public function has(string $key) : bool
    {
        if ($this->query->has(key: $key)) {
            return true;
        }

        return (bool) $this->request->has(key: $key);
    }

    /**
     * Retrieve a cookie by key with an optional default.
     *
     * @param string $key     The key of the cookie.
     * @param mixed  $default Optional default value.
     *
     * @return mixed The value of the cookie or default.
     */
    public function cookie(string $key, mixed $default = null) : mixed
    {
        return $this->cookies->get(key: $key, default: $default);
    }

    /**
     * Retrieve a file by key with an optional default.
     *
     * @param string $key     The key of the file.
     * @param mixed  $default Optional default value.
     *
     * @return mixed The value of the file or default.
     */
    public function file(string $key, mixed $default = null) : mixed
    {
        return $this->files->get(key: $key, default: $default);
    }

    /**
     * Retrieve data from a JSON input.
     *
     * Decodes JSON content if not already done and retrieves the specified key,
     * or all JSON data if no key is specified.
     *
     * @param string|null $key     The key of the JSON data.
     * @param mixed       $default Optional default value.
     *
     * @return mixed The value of the JSON data or default.
     * @throws RuntimeException If JSON decoding fails.
     */
    public function json(string|null $key = null, mixed $default = null) : mixed
    {
        $content = $this->getContent();

        try {
            // Decode raw request content as JSON into array format
            $data = json_decode(json: (string) ($content ?: '{}'), associative: true, depth: 512, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $jsonException) {
            // Wrap JSON parsing failure in a descriptive runtime exception
            throw new RuntimeException(
                message : 'Failed to decode JSON: ' . $jsonException->getMessage(),
                code    : $jsonException->getCode(),
                previous: $jsonException
            );
        }

        // Wrap JSON array inside Arrhae for advanced dot-access
        $this->json = new ParameterBag(parameters: (array) $data);
        $arrhae     = Arrhae::make(items: $this->json->all());

        // Return an entire array if no key, or safely fetch nested value using Arrhae
        return is_null(value: $key) ? $arrhae->all() : $arrhae->get(key: $key, default: $default);
    }


    /**
     * Retrieve the raw content of the request body.
     *
     * @return string The request body content.
     */
    public function getContent() : string
    {
        $this->body->rewind();

        return $this->body->getContents();
    }

    /**
     * Merge additional data into query and/or request parameters.
     *
     * Allows for optional merging into query and/or request parameters with control
     * over whether to overwrite existing parameters.
     *
     * @param array     $data        The data to merge.
     * @param bool|null $intoQuery   Whether to merge into query parameters.
     * @param bool|null $intoRequest Whether to merge into request parameters.
     * @param bool      $overwrite   Whether to overwrite existing parameters.
     */
    public function merge(
        array     $data,
        bool|null $intoQuery = null,
        bool|null $intoRequest = null,
        bool      $overwrite = true,
    ) : void {
        $intoRequest ??= true;
        $intoQuery   ??= true;
        foreach ($data as $key => $value) {
            if ($intoQuery && ($overwrite || ! $this->query->has(key: $key))) {
                $this->query->set(key: $key, value: $value);
            }

            if ($intoRequest && ($overwrite || ! $this->request->has(key: $key))) {
                $this->request->set(key: $key, value: $value);
            }
        }
    }

    /**
     * Retrieve the bearer token from the 'Authorization' header, if available.
     *
     * This method checks for a Bearer token in the Authorization header:
     * - It calls `getHeaderLine('Authorization')` to retrieve the Authorization header as a single string.
     * - If the header starts with "Bearer ", the function extracts the token and returns it.
     * - If there is no Bearer token, it returns `null`.
     *
     * @return string|null The Bearer token string if present, or null if not found.
     */
    public function getBearerToken() : string|null
    {
        // Retrieve the Authorization header as a single line using getHeaderLine
        $authHeader = $this->getHeaderLine(name: 'Authorization');

        // Check if the header starts with "Bearer " and, if so, extract the token part
        if (str_starts_with(haystack: (string) $authHeader, needle: 'Bearer ')) {
            return substr(string: (string) $authHeader, offset: 7);
        }

        // Return null if no Bearer token is present in the Authorization header
        return null;
    }

    /**
     * Retrieve a single header line by its name.
     *
     * This method accesses the request headers to fetch a specific header line.
     * - If the header is found with multiple values, they are concatenated into a single comma-separated string.
     * - If the header does not exist, it returns an empty string.
     *
     * @param string $name The name of the header (case-insensitive).
     *
     * @return string The header value as a single string or an empty string if the header does not exist.
     */
    #[\Override]
    public function getHeaderLine(string $name) : string
    {
        // Use the request method to access headers, normalizing the header name to lowercase for consistency
        $header = $this->request(key: 'headers.' . strtolower(string: $name));

        // If the header exists and contains multiple values, convert the array to a comma-separated string
        if ($header !== null) {
            return is_array(value: $header) ? implode(separator: ', ', array: $header) : (string) $header;
        }

        // Return an empty string if the header is not found
        return '';
    }

    /**
     * Retrieve a parameter from any input bag.
     *
     * Iterates through all input bags until the parameter is found.
     *
     * @param string $key     The key of the input.
     * @param mixed  $default Optional default value.
     *
     * @return mixed The value of the input or default.
     */
    public function request(string $key, mixed $default = null) : mixed
    {
        foreach ($this->allBags() as $bag) {
            if ($bag->has($key)) {
                return $bag->get($key);
            }
        }

        return $default;
    }

    /**
     * Retrieve all input bags.
     *
     * @return array An array of all input bags.
     */
    public function allBags() : array
    {
        return [
            'query'   => $this->query,
            'request' => $this->request,
            'cookies' => $this->cookies,
            'files'   => $this->files,
        ];
    }

    private function parseJsonBody() : array
    {
        $contentType = $this->getHeaderLine(name: 'Content-Type');

        // Proverava da li je zahtev JSON
        if (str_contains(haystack: $contentType, needle: 'application/json')) {
            $rawBody = (string) $this->getBody();

            if (! empty($rawBody)) {
                $decoded = json_decode(json: $rawBody, associative: true);

                return is_array(value: $decoded) ? $decoded : [];
            }
        }

        return [];
    }

}
