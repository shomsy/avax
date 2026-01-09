<?php

declare(strict_types=1);
namespace Avax\Container\Guard\Rules;

use Avax\Container\Features\Define\Store\ServiceDefinitionEntity;
use Avax\DataHandling\Validation\Attributes\AbstractRule;
use ReflectionClass;

/**
 * Validation rule for service class definitions in dependency injection containers.
 *
 * This attribute-based validation rule ensures that service classes meet container
 * requirements for safe dependency injection. It performs comprehensive checks
 * on class existence, instantiability, interface compliance, and security constraints.
 *
 * ARCHITECTURAL ROLE:
 * - Guards against invalid service registrations
 * - Enforces container-specific class requirements
 * - Provides early validation during service definition
 * - Supports flexible configuration for different use cases
 *
 * VALIDATION CAPABILITIES:
 * - Class/interface existence verification
 * - Abstract class and interface allowance control
 * - Required interface implementation checking
 * - Forbidden class blacklist enforcement
 * - Security constraint validation
 *
 * USAGE SCENARIOS:
 * ```php
 * // Basic service validation
 * #[ServiceValidationRule]
 * public string $databaseService;
 *
 * // Advanced validation with constraints
 * #[ServiceValidationRule(
 *     allowAbstract: false,
 *     requiredInterfaces: [LoggerInterface::class],
 *     forbiddenClasses: [DebugLogger::class]
 * )]
 * public string $loggerService;
 * ```
 *
 * VALIDATION RULES:
 * - Service must be non-empty string
 * - Target class or interface must exist
 * - Abstract classes allowed only if configured
 * - Interfaces allowed only if configured
 * - Required interfaces must be implemented
 * - Forbidden classes are rejected
 *
 * PERFORMANCE CONSIDERATIONS:
 * - Reflection operations for class inspection
 * - Array operations for constraint checking
 * - Minimal overhead for simple validations
 * - Caching recommended for repeated validations
 *
 * SECURITY IMPLICATIONS:
 * - Prevents registration of non-existent classes
 * - Blocks forbidden classes for security
 * - Enforces interface contracts for type safety
 * - Validates abstract class usage appropriately
 *
 * @package Avax\Container\Guard\Rules
 * @see     AbstractRule For base validation rule functionality
 * @see     ServiceDefinitionEntity For service definition structure
 * @see     docs_md/Guard/Rules/ServiceValidationRule.md#quick-summary
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ServiceValidationRule extends AbstractRule
{
    /**
     * Creates a new service validation rule with specified constraints.
     *
     * Initializes the validation rule with flexible configuration options
     * that control what types of classes are acceptable for service registration.
     *
     * CONSTRAINT CONFIGURATION:
     * - allowAbstract: Permits abstract classes when true
     * - allowInterface: Permits interfaces when true
     * - requiredInterfaces: Classes must implement all listed interfaces
     * - forbiddenClasses: Explicitly blocked class names
     *
     * DEFAULT BEHAVIOR:
     * - Abstract classes and interfaces are rejected
     * - No required interfaces enforced
     * - No forbidden classes configured
     *
     * @param bool  $allowAbstract      Whether abstract classes are permitted
     * @param bool  $allowInterface     Whether interfaces are permitted
     * @param array $requiredInterfaces Interfaces that must be implemented
     * @param array $forbiddenClasses   Class names that are explicitly blocked
     * @see docs_md/Guard/Rules/ServiceValidationRule.md#method-__construct
     */
    public function __construct(
        private readonly bool  $allowAbstract = false,
        private readonly bool  $allowInterface = false,
        private readonly array $requiredInterfaces = [],
        private readonly array $forbiddenClasses = []
    ) {}

    /**
     * Validates a service class name against configured constraints.
     *
     * Performs comprehensive validation of the provided class name to ensure
     * it meets all container requirements for service registration. Returns
     * true if the class is valid for use as a service, false otherwise.
     *
     * VALIDATION SEQUENCE:
     * 1. Type check: Must be non-empty string
     * 2. Existence check: Class/interface must exist
     * 3. Interface check: Interfaces allowed based on configuration
     * 4. Forbidden check: Class not in forbidden list
     * 5. Abstract check: Abstract classes allowed based on configuration
     * 6. Interface check: Required interfaces must be implemented
     *
     * SHORT-CIRCUIT BEHAVIOR:
     * - Returns false immediately on first failed check
     * - No exceptions thrown during validation
     * - Safe to call with invalid input
     *
     * @param mixed $value The value to validate (expected to be a class name string)
     *
     * @return bool True if the class name passes all validation checks
     * @throws \ReflectionException
     * @see docs_md/Guard/Rules/ServiceValidationRule.md#method-validate
     */
    public function validate(mixed $value) : bool
    {
        if (! is_string($value) || empty(trim($value))) {
            return false;
        }

        $className = trim($value);

        // Check if class/interface exists
        if (! class_exists($className) && ! interface_exists($className)) {
            return false;
        }

        // If it's an interface, check if allowed
        if (interface_exists($className)) {
            return $this->allowInterface;
        }

        // Check if class is allowed
        if (in_array($className, $this->forbiddenClasses, true)) {
            return false;
        }

        // Check if abstract classes are allowed
        if (! $this->allowAbstract && (new ReflectionClass(objectOrClass: $className))->isAbstract()) {
            return false;
        }

        // Check required interfaces
        foreach ($this->requiredInterfaces as $interface) {
            if (! is_subclass_of($className, $interface) &&
                ! in_array($interface, class_implements($className))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generates a descriptive error message for validation failures.
     *
     * Creates a human-readable error message that explains why a service
     * class failed validation. The message includes specific constraint
     * information to help developers understand and fix validation issues.
     *
     * MESSAGE CONSTRUCTION:
     * - Lists all active constraints from configuration
     * - Provides specific guidance for each constraint type
     * - Uses clear, actionable language
     * - Falls back to generic message if no constraints configured
     *
     * CONSTRAINT MESSAGES:
     * - Interfaces: "interfaces not allowed"
     * - Abstract: "abstract classes not allowed"
     * - Required: "must implement: Interface1, Interface2"
     * - Forbidden: "class not in forbidden list"
     *
     * USAGE IN ERROR REPORTING:
     * ```php
     * if (!$rule->validate($className)) {
     *     $error = "Invalid service class: " . $rule->getErrorMessage();
     *     throw new ValidationException($error);
     * }
     * ```
     *
     * @return string Descriptive error message explaining validation failure
     * @see docs_md/Guard/Rules/ServiceValidationRule.md#method-geterrormessage
     */
    public function getErrorMessage() : string
    {
        $parts = [];

        if (! $this->allowInterface) {
            $parts[] = 'interfaces not allowed';
        }

        if (! $this->allowAbstract) {
            $parts[] = 'abstract classes not allowed';
        }

        if (! empty($this->requiredInterfaces)) {
            $parts[] = 'must implement: ' . implode(', ', $this->requiredInterfaces);
        }

        if (! empty($this->forbiddenClasses)) {
            $parts[] = 'class not in forbidden list';
        }

        $constraints = empty($parts) ? 'must be a valid class' : implode(', ', $parts);

        return "Service class {$constraints}";
    }
}
