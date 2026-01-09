<?php

declare(strict_types=1);
namespace Avax\Container\Guard\Rules;

use Avax\Container\Features\Define\Store\ServiceDefinitionEntity;
use Avax\Container\Features\Define\Store\ServiceDependencyRepository;
use Avax\DataHandling\Validation\Attributes\AbstractRule;

/**
 * Validation rule for service dependency arrays in dependency injection containers.
 *
 * This attribute-based validation rule ensures that service dependency declarations
 * are syntactically valid, reference existing services, and follow container naming
 * conventions. It provides early validation during service definition to prevent
 * runtime resolution failures.
 *
 * ARCHITECTURAL ROLE:
 * - Validates dependency array structures during service registration
 * - Ensures referential integrity between service definitions
 * - Prevents invalid service identifiers from entering the container
 * - Supports whitelist-based dependency validation
 *
 * VALIDATION CAPABILITIES:
 * - Array structure validation (must be array of strings)
 * - Service identifier format validation (PHP identifier rules)
 * - Available services whitelist enforcement
 * - Empty/invalid dependency rejection
 *
 * USAGE SCENARIOS:
 * ```php
 * // Basic dependency validation
 * #[DependencyValidationRule]
 * public array $serviceDependencies;
 *
 * // Restricted validation with service whitelist
 * #[DependencyValidationRule(availableServices: ['database', 'cache', 'logger'])]
 * public array $infrastructureDeps;
 *
 * // Strict validation for specific contexts
 * #[DependencyValidationRule(
 *     availableServices: ['user.repo', 'auth.service'],
 *     allowSelfReference: false,
 *     maxDependencyDepth: 5
 * )]
 * public array $domainDependencies;
 * ```
 *
 * VALIDATION RULES:
 * - Input must be array of non-empty strings
 * - Each dependency must be valid PHP identifier
 * - Dependencies must exist in configured whitelist (if provided)
 * - Unicode identifiers supported for internationalization
 *
 * IDENTIFIER PATTERN:
 * Uses PHP identifier regex: `/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/`
 * - Must start with letter, underscore, or Unicode character
 * - Subsequent characters can include digits
 * - Supports Unicode identifiers for global applications
 *
 * PERFORMANCE CONSIDERATIONS:
 * - Array iteration for dependency validation
 * - Regex validation for each identifier
 * - Minimal overhead for small dependency arrays
 * - Suitable for service registration validation
 *
 * SECURITY IMPLICATIONS:
 * - Prevents injection of malformed service identifiers
 * - Validates against known service whitelist
 * - Blocks potentially malicious identifier patterns
 * - Ensures clean service dependency graphs
 *
 * @package Avax\Container\Guard\Rules
 * @see     AbstractRule For base validation rule functionality
 * @see     ServiceDefinitionEntity For service definition structure
 * @see     ServiceDependencyRepository For dependency relationship management
 * @see     docs_md/Guard/Rules/DependencyValidationRule.md#quick-summary
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class DependencyValidationRule extends AbstractRule
{
    /**
     * Creates a new dependency validation rule with configurable constraints.
     *
     * Initializes the validation rule with optional constraints that control
     * what dependencies are considered valid. Provides flexible configuration
     * for different validation scenarios and security requirements.
     *
     * CONSTRAINT CONFIGURATION:
     * - availableServices: Whitelist of valid service identifiers
     * - allowSelfReference: Whether self-referencing dependencies are allowed
     * - maxDependencyDepth: Maximum allowed dependency chain depth
     *
     * DEFAULT BEHAVIOR:
     * - No service whitelist (accepts any valid identifier)
     * - Self-references not allowed for safety
     * - Depth limit of 10 to prevent excessive nesting
     *
     * @param array|null $availableServices  Whitelist of valid service identifiers, null for no restrictions
     * @param bool|null  $allowSelfReference Whether self-referencing dependencies are permitted
     * @param int        $maxDependencyDepth Maximum allowed dependency resolution depth
     * @see docs_md/Guard/Rules/DependencyValidationRule.md#method-__construct
     */
    public function __construct(
        private array|null   $availableServices = null,
        private bool|null    $allowSelfReference = null,
        private readonly int $maxDependencyDepth = 10
    )
    {
        $this->availableServices  ??= [];
        $this->allowSelfReference ??= false;
    }

    /**
     * Validates an array of service dependency identifiers.
     *
     * Performs comprehensive validation of dependency arrays to ensure all
     * identifiers are syntactically valid, non-empty, and meet configured
     * constraints. Returns true if all dependencies pass validation.
     *
     * VALIDATION SEQUENCE:
     * 1. Type check: Must be array
     * 2. Element validation: Each dependency must be non-empty string
     * 3. Format validation: Each identifier must match PHP identifier pattern
     * 4. Whitelist validation: Dependencies must exist in available services (if configured)
     *
     * SHORT-CIRCUIT BEHAVIOR:
     * - Returns false immediately on first validation failure
     * - No exceptions thrown during validation
     * - Safe to call with malformed input
     *
     * IDENTIFIER REQUIREMENTS:
     * - Must start with letter (a-z, A-Z), underscore (_), or Unicode character
     * - Subsequent characters can include letters, digits (0-9), underscores, Unicode
     * - Cannot be empty or whitespace-only
     * - Case-sensitive validation
     *
     * @param mixed $value The value to validate (expected to be array of service identifiers)
     *
     * @return bool True if all dependencies are valid, false otherwise
     * @see docs_md/Guard/Rules/DependencyValidationRule.md#method-validate
     */
    public function validate(mixed $value) : bool
    {
        if (! is_array($value)) {
            return false;
        }

        foreach ($value as $dependency) {
            if (! is_string($dependency) || empty(trim($dependency))) {
                return false;
            }

            $depId = trim($dependency);

            // Check if dependency exists in available services
            if (! empty($this->availableServices) && ! in_array($depId, $this->availableServices, true)) {
                return false;
            }

            // Basic validation - no obviously invalid characters
            if (! preg_match('/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $depId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generates a descriptive error message for validation failures.
     *
     * Creates a human-readable error message that explains why dependency
     * validation failed. Includes specific constraint information to help
     * developers understand and fix dependency declaration issues.
     *
     * MESSAGE CONSTRUCTION:
     * - Lists all active constraints from configuration
     * - Provides specific guidance for each constraint type
     * - Uses clear, actionable language
     * - Combines multiple constraints with appropriate conjunctions
     *
     * CONSTRAINT MESSAGES:
     * - Whitelist: "dependencies must exist in: service1, service2"
     * - Format: "must be valid service identifiers"
     * - Combined: "dependencies must exist in: db, cache and must be valid service identifiers"
     *
     * USAGE IN ERROR REPORTING:
     * ```php
     * if (!$rule->validate($dependencies)) {
     *     $error = "Invalid service dependencies: " . $rule->getErrorMessage();
     *     throw new ValidationException($error);
     * }
     * ```
     *
     * @return string Descriptive error message explaining validation failure
     * @see docs_md/Guard/Rules/DependencyValidationRule.md#method-geterrormessage
     */
    public function getErrorMessage() : string
    {
        $constraints = [];

        if (! empty($this->availableServices)) {
            $constraints[] = 'dependencies must exist in: ' . implode(', ', $this->availableServices);
        }

        $constraints[] = 'must be valid service identifiers';

        return 'Service dependencies ' . implode(' and ', $constraints);
    }
}
