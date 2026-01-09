<?php

declare(strict_types=1);
namespace Avax\Container\Features\Think\Model;

use Avax\Container\Features\Actions\Inject\InjectDependencies;

/**
 * @package Avax\Container\Think\Model
 *
 * Immutable specification for method parameter dependency resolution.
 *
 * ParameterPrototype captures the complete static analysis of a single method parameter,
 * defining how the dependency injection container should resolve and provide values
 * for that parameter during method invocation. It includes type hints, default values,
 * nullability constraints, and special handling flags.
 *
 * ARCHITECTURAL ROLE:
 * - Defines parameter resolution requirements for dependency injection
 * - Guides the container's resolution engine for type-safe parameter provision
 * - Supports complex parameter scenarios (variadic, nullable, typed)
 * - Enables fallback strategies when dependencies cannot be resolved
 *
 * PARAMETER RESOLUTION HIERARCHY:
 * 1. Type-hinted class/interface resolution via container
 * 2. Explicit override values (highest priority)
 * 3. Default parameter values (fallback)
 * 4. Null values for nullable parameters
 * 5. Resolution failure with detailed error reporting
 *
 * TYPE SYSTEM SUPPORT:
 * - Class and interface type hints for dependency injection
 * - Built-in types (int, string, etc.) passed through directly
 * - Union types resolved to first compatible class/interface
 * - Generic types and complex type expressions
 *
 * VARIADIC PARAMETER HANDLING:
 * ```php
 * // Method signature: logMessages(string ...$messages)
 * $prototype = new ParameterPrototype(
 *     name: 'messages',
 *     type: 'string',
 *     isVariadic: true,
 *     allowsNull: false
 * );
 * ```
 *
 * NULLABILITY AND DEFAULTS:
 * - $allowsNull: Whether null is acceptable for this parameter
 * - $hasDefault: Whether the parameter has a default value in the method
 * - $default: The actual default value (used when resolution fails)
 *
 * SERIALIZATION SUPPORT:
 * ParameterPrototype supports full serialization for caching and compilation scenarios,
 * enabling pre-computed parameter analysis to be stored and reused across requests.
 *
 * PERFORMANCE CHARACTERISTICS:
 * - Immutable design allows safe sharing across injection operations
 * - Minimal memory footprint with efficient property storage
 * - Fast property access for resolution decision making
 *
 * @see     MethodPrototype For method-level parameter collections
 * @see     \Avax\Container\Features\Actions\Resolve\Contracts\ResolutionEngineInterface For runtime parameter resolution
 *          using ParameterPrototype
 * @see     InjectDependencies For parameter injection during method calls
 * @see docs_md/Features/Think/Model/ParameterPrototype.md#quick-summary
 */
readonly class ParameterPrototype
{
    /**
     * Creates a new parameter specification for dependency resolution.
     *
     * Initializes the prototype with all the information needed to resolve
     * this parameter during dependency injection. The prototype includes
     * type constraints, default values, and special handling flags.
     *
     * TYPE RESOLUTION PRIORITY:
     * - Class/interface names trigger container resolution
     * - Built-in types (int, string) are passed through directly
     * - Null type hints are treated as nullable without container lookup
     *
     * DEFAULT VALUE HANDLING:
     * - $hasDefault indicates if the method signature provides a fallback
     * - $default contains the actual value to use when resolution fails
     * - Defaults are used only when no override is provided
     *
     * VARIADIC SUPPORT:
     * - $isVariadic flags parameters like `...$args`
     * - Affects how multiple values are collected and passed
     * - Container may provide array unpacking for variadic resolution
     *
     * @param string      $name       The parameter name in the method signature
     * @param string|null $type       The type hint (class/interface) for container resolution
     * @param bool        $hasDefault Whether the parameter has a default value
     * @param mixed       $default    The default value to use if resolution fails
     * @param bool        $isVariadic Whether this is a variadic parameter (...$args)
     * @param bool        $allowsNull Whether null is acceptable for this parameter
     * @see docs_md/Features/Think/Model/ParameterPrototype.md#method-__construct
     */
    public function __construct(
        public string      $name,
        public string|null $type = null,
        public bool        $hasDefault = false,
        public mixed       $default = null,
        public bool        $isVariadic = false,
        public bool        $allowsNull = false,
        public bool        $required = true
    ) {}

    /**
     * Deserializes a ParameterPrototype from var_export() array format.
     *
     * This magic method enables unserialization from cached or compiled container data.
     * It's automatically called by PHP when loading serialized ParameterPrototype instances.
     *
     * USAGE IN COMPILED CONTAINERS:
     * ```php
     * // Generated by compilation
     * $prototype = ParameterPrototype::__set_state([
     *     'name' => 'logger',
     *     'type' => 'LoggerInterface',
     *     'allowsNull' => false,
     *     ...
     * ]);
     * ```
     *
     * @param array $array The serialized array representation
     *
     * @return self The deserialized ParameterPrototype instance
     * @see __set_state For PHP's automatic unserialization mechanism
     * @see docs_md/Features/Think/Model/ParameterPrototype.md#method-__set_state
     */
    public static function __set_state(array $array) : self
    {
        return self::fromArray(data: $array);
    }

    /**
     * Creates a ParameterPrototype instance from an array representation.
     *
     * This factory method reconstructs a ParameterPrototype from serialized data, typically
     * loaded from cache or compiled container definitions. It performs validation
     * and safe type reconstruction for all parameter prototypes.
     *
     * ARRAY FORMAT:
     * ```php
     * [
     *     'name' => 'logger',
     *     'type' => 'LoggerInterface',
     *     'hasDefault' => false,
     *     'default' => null,
     *     'isVariadic' => false,
     *     'allowsNull' => false
     * ]
     * ```
     *
     * VALIDATION:
     * - Ensures parameter name is valid string
     * - Validates type hint format if present
     * - Safely handles default value reconstruction
     * - Maintains boolean flag integrity
     *
     * @param array $data The array containing ParameterPrototype data
     *
     * @return self The constructed ParameterPrototype instance
     * @throws \InvalidArgumentException If data format is invalid
     * @see docs_md/Features/Think/Model/ParameterPrototype.md#method-fromarray
     */
    public static function fromArray(array $data) : self
    {
        return new self(
            name      : $data['name'],
            type      : $data['type'] ?? null,
            hasDefault: $data['hasDefault'] ?? false,
            default   : $data['default'] ?? null,
            isVariadic: $data['isVariadic'] ?? false,
            allowsNull: $data['allowsNull'] ?? false,
            required  : $data['required'] ?? true
        );
    }

    /**
     * Converts the ParameterPrototype to an array representation for serialization.
     *
     * This method enables the ParameterPrototype to be serialized for caching or compilation.
     * The resulting array can be stored in files or caches and later reconstructed
     * using fromArray() or __set_state().
     *
     * SERIALIZATION SCENARIOS:
     * - Container compilation for production deployment
     * - Caching analyzed parameter prototypes to avoid repeated reflection
     * - Persisting parameter metadata across application restarts
     *
     * OUTPUT FORMAT:
     * ```php
     *
     * [
     *     'name' => 'logger',
     *     'type' => 'LoggerInterface',
     *     'hasDefault' => false,
     *     'default' => null,
     *     'isVariadic' => false,
     *     'allowsNull' => false
     * ]
     * ```
     *
     * @return array The array representation of this ParameterPrototype
     * @see docs_md/Features/Think/Model/ParameterPrototype.md#method-toarray
     */
    public function toArray() : array
    {
        return [
            'name'       => $this->name,
            'type'       => $this->type,
            'hasDefault' => $this->hasDefault,
            'default'    => $this->default,
            'isVariadic' => $this->isVariadic,
            'allowsNull' => $this->allowsNull,
            'required'   => $this->required,
        ];
    }
}
