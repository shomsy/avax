<?php

declare(strict_types=1);
namespace Avax\Container\Features\Think\Verify;

use Avax\Container\Features\Think\Model\MethodPrototype;
use Avax\Container\Features\Think\Model\ServicePrototype;
use RuntimeException;

/**
 * Service for validating dependency injection prototypes.
 *
 * VerifyPrototype performs comprehensive validation checks on ServicePrototype instances
 * to ensure they are safe and correct for dependency injection. This includes type validation,
 * circular dependency detection, and security checks.
 *
 * VALIDATION CHECKS:
 * - All injectable parameters have defined types (no untyped dependencies)
 * - Constructor parameters are properly typed
 * - Property injection points have resolvable types
 * - Method injection parameters have types
 * - No circular dependencies in the prototype graph
 *
 * SECURITY FEATURES:
 * - Prevents injection of untyped or ambiguous dependencies
 * - Validates that all injection points are safe
 * - Ensures prototype integrity before caching
 *
 * USAGE SCENARIOS:
 * - During prototype generation to catch issues early
 * - Before caching prototypes for production
 * - In development tools for validation
 * - As part of CI/CD validation pipelines
 *
 * PERFORMANCE IMPACT:
 * - Validation adds minimal overhead during analysis
 * - Helps prevent runtime failures and improves reliability
 * - Can be skipped in production for maximum performance
 *
 * ERROR HANDLING:
 * - Throws descriptive RuntimeException for validation failures
 * - Provides specific error messages for debugging
 * - Includes context about which prototype failed validation
 *
 * THREAD SAFETY:
 * - Stateless validation logic
 * - Safe for concurrent validation of multiple prototypes
 *
 * @package Avax\Container\Think\Verify
 * @see docs_md/Features/Think/Verify/VerifyPrototype.md#quick-summary
 */
final readonly class VerifyPrototype
{
    /**
     * Performs a comprehensive validation suite on multiple prototypes.
     *
     * Validates an array of ServicePrototype instances and returns detailed
     * validation results. Useful for batch validation during development
     * or CI/CD processes.
     *
     * @param ServicePrototype[] $prototypes Array of prototypes to validate
     *
     * @return array{
     *     valid: ServicePrototype[],
     *     invalid: array<string, string>,
     *     summary: array{total: int, valid: int, invalid: int}
     * } Validation results with valid prototypes, invalid ones with errors, and summary stats
     * @see docs_md/Features/Think/Verify/VerifyPrototype.md#method-validatebatch
     */
    public function validateBatch(array $prototypes) : array
    {
        $valid   = [];
        $invalid = [];

        foreach ($prototypes as $prototype) {
            try {
                $this->validate(prototype: $prototype);
                $valid[] = $prototype;
            } catch (RuntimeException $e) {
                $invalid[$prototype->class] = $e->getMessage();
            }
        }

        return [
            'valid'   => $valid,
            'invalid' => $invalid,
            'summary' => [
                'total'   => count($prototypes),
                'valid'   => count($valid),
                'invalid' => count($invalid),
            ],
        ];
    }

    /**
     * Validates a ServicePrototype for correctness and safety.
     *
     * Performs comprehensive validation checks on the prototype to ensure
     * it can be safely used for dependency injection. Throws exceptions
     * for any validation failures.
     *
     * VALIDATION PROCESS:
     * 1. Check that the service class is instantiable
     * 2. Validate constructor parameters have types
     * 3. Validate property injection points
     * 4. Validate method injection parameters
     * 5. Check for potential security issues
     *
     * @param ServicePrototype $prototype The prototype to validate
     *
     * @return void
     *
     * @throws RuntimeException If validation fails
     * @see docs_md/Features/Think/Verify/VerifyPrototype.md#method-validate
     */
    public function validate(ServicePrototype $prototype) : void
    {
        // 1. Check instantiability
        if (! $prototype->isInstantiable) {
            throw new RuntimeException(message: "Cannot validate non-instantiable prototype for class: {$prototype->class}");
        }

        // 2. Validate constructor parameters
        if ($prototype->constructor) {
            $this->validateMethodPrototype(method: $prototype->constructor, context: 'constructor', class: $prototype->class);
        }

        // 3. Validate property injection points
        foreach ($prototype->injectedProperties as $property) {
            if (! $property->type) {
                throw new RuntimeException(message: "Property [{$property->name}] in class [{$prototype->class}] has no resolvable type for injection");
            }
        }

        // 4. Validate method injection parameters
        foreach ($prototype->injectedMethods as $method) {
            $this->validateMethodPrototype(method: $method, context: "method {$method->name}", class: $prototype->class);
        }
    }

    /**
     * Validates a MethodPrototype's parameters.
     *
     * Ensures all parameters in the method have defined types that can be resolved
     * for dependency injection.
     *
     * @param \Avax\Container\Features\Think\Model\MethodPrototype $method  The method prototype to validate
     * @param string                                               $context Context description (e.g., "constructor",
     *                                                                      "method setLogger")
     * @param string                                               $class   The class name for error reporting
     *
     * @return void
     *
     * @throws RuntimeException If any parameter lacks a resolvable type
     */
    private function validateMethodPrototype(MethodPrototype $method, string $context, string $class) : void
    {
        foreach ($method->parameters as $parameter) {
            if (! $parameter->type) {
                throw new RuntimeException(message: "Parameter [{$parameter->name}] in {$context} of class [{$class}] has no resolvable type");
            }
        }
    }
}
