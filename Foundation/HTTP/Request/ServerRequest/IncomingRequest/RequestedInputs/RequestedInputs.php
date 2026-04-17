<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\AccessesTypedValues;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\InputValue;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\MergedInputs;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\ParsedBody;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Inputs\QueryParams;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Mapping\MapRequestedInputsToDto;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization\InputSanitizer;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Sanitization\SanitizedRequestedInputs;
use BackedEnum;
use RuntimeException;

/**
 * RequestedInputs
 *
 * Root flow-owner for the RequestedInputs feature.
 *
 * Public API is intentionally pipeline-oriented for readability.
 * Internal stages are exposed via private/protected methods so the
 * top-level flow can be expressed clearly without exposing helpers.
 *
 * Typical usage:
 *
 *     $inputs = RequestedInputs::fromSlices($query, $body);
 *     $email   = $inputs->string('email');
 *     $safe    = $inputs->sanitized()->html('content');
 *     $command = $inputs->as(CreateUserCommand::class);
 *
 * Key semantic rules preserved:
 *   - key presence uses array_key_exists()
 *   - null means "present but null"
 *   - body wins over query on collision
 */
final readonly class RequestedInputs
{
    use AccessesTypedValues;

    private MergedInputs $merged;

    private InputSanitizer $sanitizer;

    private MapRequestedInputsToDto $mapper;

    /**
     * @param array<string, mixed>         $queryParams
     * @param array|object|null            $parsedBody
     * @param InputSanitizer|null          $sanitizer
     * @param MapRequestedInputsToDto|null $mapper
     */
    public function __construct(
        array|null                   $queryParams = null,
        array|object|null            $parsedBody = null,
        InputSanitizer|null          $sanitizer = null,
        MapRequestedInputsToDto|null $mapper = null,
    )
    {
        $queryParams     ??= [];
        $this->merged    = $this->createMergedInputs(queryParams: $queryParams, parsedBody: $parsedBody);
        $this->sanitizer = $sanitizer ?? new InputSanitizer;
        $this->mapper    = $mapper ?? new MapRequestedInputsToDto;
    }

    protected function createMergedInputs(array $queryParams, array|object|null $parsedBody) : MergedInputs
    {
        return new MergedInputs(
            queryParams: QueryParams::fromArray(params: $queryParams),
            parsedBody : ParsedBody::fromArray(data: $parsedBody),
        );
    }

    /**
     * Create from an associative array treated as merged input.
     *
     * Note: this is a convenience constructor for simple cases
     * where input is already merged. It is semantically distinct
     * from fromSlices because there is no separate query/body split.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data) : self
    {
        return new self(queryParams: [], parsedBody: $data);
    }

    /**
     * @param array<string, mixed> $queryParams
     * @param array|object|null    $parsedBody
     *
     * @return RequestedInputs
     */
    public static function fromSlices(
        array|null        $queryParams = null,
        array|object|null $parsedBody = null,
    ) : self
    {
        $queryParams ??= [];

        return new self(queryParams: $queryParams, parsedBody: $parsedBody);
    }

    // ---------------------------------------------------------------------
    // Stage 1: Source intake
    // ---------------------------------------------------------------------

    public function data() : array
    {
        return $this->merged->all();
    }

    /**
     * Return all merged inputs.
     *
     * @return array<string, mixed>
     */
    public function all() : array
    {
        return $this->merged->all();
    }

    // ---------------------------------------------------------------------
    // Stage 2: Merge stage
    // ---------------------------------------------------------------------

    /**
     * Get a value by key with source precedence:
     * body > query > default.
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->merged->get(key: $key, default: $default);
    }

    // ---------------------------------------------------------------------
    // Stage 3: Typed read access (the primary read path)
    // ---------------------------------------------------------------------

    /**
     * Check if key exists in either query or body.
     *
     * Uses array_key_exists() so a key present with null value returns true.
     */
    public function has(string $key) : bool
    {
        return $this->merged->has(key: $key);
    }

    /**
     * Check if key exists and is not null.
     */
    public function hasNonNull(string $key) : bool
    {
        return $this->merged->hasNonNull(key: $key);
    }

    /**
     * Access only from body.
     */
    public function fromBody(string $key, mixed $default = null) : mixed
    {
        return $this->merged->fromBody(key: $key, default: $default);
    }

    /**
     * Access only from query.
     */
    public function fromQuery(string $key, mixed $default = null) : mixed
    {
        return $this->merged->fromQuery(key: $key, default: $default);
    }

    /**
     * Return source-aware input value wrapper.
     */
    public function value(string $key, mixed $default = null) : InputValue
    {
        return $this->merged->value(key: $key, default: $default);
    }

    public function text(string $key, string $default = '') : string
    {
        return $this->string(key: $key, default: $default);
    }

    public function string(string $key, string $default = '') : string
    {
        return $this->merged->string(key: $key, default: $default);
    }

    // ---------------------------------------------------------------------
    // Stage 4: Convenience accessors (typed)
    // ---------------------------------------------------------------------

    public function int(string $key, int $default = 0) : int
    {
        return $this->merged->int(key: $key, default: $default);
    }

    public function float(string $key, float $default = 0.0) : float
    {
        return $this->merged->float(key: $key, default: $default);
    }

    public function bool(string $key, bool $default = false) : bool
    {
        return $this->merged->bool(key: $key, default: $default);
    }

    /**
     * @param array<string, mixed> $default
     *
     * @return array<string, mixed>
     */
    public function array(string $key, array $default = []) : array
    {
        return $this->merged->array(key: $key, default: $default);
    }

    /**
     * @template T of BackedEnum
     *
     * @param class-string<T> $enumClass
     *
     * @return T|null
     */
    public function enum(string $key, string $enumClass, mixed $default = null) : BackedEnum|null
    {
        return $this->merged->enum(key: $key, enumClass: $enumClass, default: $default);
    }

    public function sanitizedHtml(string $key, string $default = '') : string
    {
        return $this->sanitized()->html(key: $key, default: $default);
    }

    public function sanitized() : SanitizedRequestedInputs
    {
        return new SanitizedRequestedInputs(
            inputs   : $this,
            sanitizer: $this->sanitizer,
        );
    }

    // ---------------------------------------------------------------------
    // Stage 5: Sanitized read views
    // ---------------------------------------------------------------------

    public function sanitizedJs(string $key, string $default = '') : string
    {
        return $this->sanitized()->js(key: $key, default: $default);
    }

    public function sanitizedPath(string $key, string $default = '') : string
    {
        return $this->sanitized()->path(key: $key, default: $default);
    }

    public function sanitizedRegex(string $key, string $default = '') : string
    {
        return $this->sanitized()->regex(key: $key, default: $default);
    }

    public function strippedControls(string $key, string $default = '') : string
    {
        return $this->sanitized()->withoutControls(key: $key, default: $default);
    }

    public function normalizedUtf8(string $key, string $default = '') : string
    {
        return $this->sanitized()->utf8(key: $key, default: $default);
    }

    /**
     * Map all merged inputs into a typed DTO using the project's
     * AbstractDTO-based DTO component.
     *
     * The DTO class must accept an array in its constructor and
     * extend AbstractDTO (which provides hydration, casting, etc.).
     *
     * @template T of AbstractDTO
     *
     * @param class-string<T> $dtoClass
     *
     * @return T
     *
     * @throws RuntimeException When DTO class cannot be instantiated
     */
    public function as(string $dtoClass) : object
    {
        return (new MapRequestedInputsToDto)->map(inputs: $this, dtoClass: $dtoClass);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rawQueryParams() : array
    {
        return $this->merged->query()->all();
    }

    // ---------------------------------------------------------------------
    // Stage 6: DTO mapping using existing DTO component
    // ---------------------------------------------------------------------

    /**
     * @return array<string, mixed>
     */
    protected function rawParsedBody() : array
    {
        return $this->merged->body()->all();
    }

    // ---------------------------------------------------------------------
    // Backward-compatible aliases (removed in pipeline-first redesign)
    // ---------------------------------------------------------------------
}
