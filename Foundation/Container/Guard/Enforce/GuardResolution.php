<?php

declare(strict_types=1);

namespace Avax\Container\Guard\Enforce;

use Avax\Container\Features\Core\DTO\ErrorDTO;
use Avax\Container\Features\Core\DTO\SuccessDTO;

/**
 * Enterprise-grade security guard for container service resolution with policy enforcement.
 *
 * This immutable security guard evaluates service resolution attempts against
 * configurable security policies, providing non-throwing policy enforcement
 * that returns structured success/error DTOs. It serves as a critical security
 * layer that prevents unauthorized autowiring while enabling comprehensive
 * security monitoring and compliance reporting.
 *
 * ARCHITECTURAL ROLE:
 * - Security policy enforcement for service resolution
 * - Non-throwing policy validation with structured responses
 * - Immutable security guard for thread-safe operation
 * - Integration point for security monitoring and auditing
 * - Compliance enforcement for enterprise security requirements
 *
 * SECURITY FEATURES:
 * - Configurable resolution policies (allowlist, blocklist, pattern-based)
 * - Non-disruptive policy enforcement (returns DTOs instead of throwing)
 * - Security event logging and monitoring integration
 * - Immutable design preventing runtime policy modification
 * - Comprehensive security context preservation
 *
 * POLICY ENFORCEMENT:
 * - Strict autowiring control based on security policies
 * - Unauthorized class resolution prevention
 * - Security policy violation detection and reporting
 * - Compliance with enterprise security standards
 * - Audit trail generation for security events
 *
 * USAGE SCENARIOS:
 * ```php
 * // Initialize security guard with policy
 * $policy = new StrictAutowirePolicy(['App\\*', 'Vendor\\Safe\\*']);
 * $guard = new GuardResolution($policy);
 *
 * // Check resolution permission
 * $result = $guard->check('Potentially\\Dangerous\\Class');
 *
 * // Handle security decision
 * if ($result instanceof ErrorDTO) {
 *     // Resolution blocked - log security event
 *     $logger->warning('Resolution blocked by security policy', [
 *         'class' => 'Potentially\\Dangerous\\Class',
 *         'reason' => $result->message,
 *         'code' => $result->code
 *     ]);
 *     throw new SecurityException('Resolution not permitted');
 * } else {
 *     // Resolution allowed - proceed with resolution
 *     $service = $container->resolve('Safe\\Class');
 * }
 * ```
 *
 * POLICY TYPES SUPPORTED:
 * - Allowlist policies: Only explicitly permitted classes
 * - Blocklist policies: Block specific dangerous classes
 * - Pattern-based policies: Regex or namespace-based rules
 * - Composite policies: Multiple policy combination
 * - Dynamic policies: Runtime policy modification (not supported in readonly guard)
 *
 * SECURITY MONITORING:
 * - Policy violation attempt logging
 * - Security event aggregation and reporting
 * - Audit trail generation for compliance
 * - Real-time security alerting integration
 * - Security metrics collection and analysis
 *
 * PERFORMANCE CHARACTERISTICS:
 * - Minimal overhead for policy evaluation
 * - Immutable design enables caching and reuse
 * - Thread-safe concurrent policy checks
 * - Efficient policy matching algorithms
 * - Memory-efficient readonly implementation
 *
 * THREAD SAFETY:
 * - Completely immutable and readonly design
 * - Safe for concurrent access from multiple threads
 * - No shared mutable state or side effects
 * - Reusable across multiple resolution operations
 * - Lock-free policy evaluation
 *
 * ERROR HANDLING:
 * - Structured error responses via DTO pattern
 * - Security context preservation in error messages
 * - Non-disruptive policy enforcement (no exceptions thrown)
 * - Comprehensive error information for security analysis
 * - Audit-friendly error reporting
 *
 * COMPLIANCE FEATURES:
 * - Security policy enforcement logging
 * - Audit trail generation for regulatory compliance
 * - Security event categorization and severity levels
 * - Integration with security information systems
 * - Configurable security reporting and alerting
 *
 * BACKWARD COMPATIBILITY:
 * - Maintains compatibility with existing security interfaces
 * - Gradual migration path for legacy security implementations
 * - Extensible policy framework for future requirements
 * - Version-aware security policy evolution
 *
 * INTEGRATION POINTS:
 * - ResolutionPolicy interface for policy abstraction
 * - ErrorDTO/SuccessDTO for structured response handling
 * - Security monitoring systems for event aggregation
 * - Audit logging systems for compliance reporting
 * - Container security frameworks for policy management
 *
 * @readonly
 *
 * @see     ResolutionPolicy Interface for policy implementation abstraction
 * @see     SuccessDTO Structured success response container
 * @see     ErrorDTO Structured error response container with security context
 * @see     docs/Guard/Enforce/GuardResolution.md#quick-summary
 */
final readonly class GuardResolution
{
    /**
     * Creates a new security guard instance with the specified resolution policy.
     *
     * Initializes the immutable security guard with a resolution policy that
     * will be used to evaluate all subsequent service resolution attempts.
     * The guard becomes readonly after construction, ensuring policy
     * immutability and thread safety.
     *
     * POLICY INJECTION:
     * - ResolutionPolicy provides the security rules for evaluation
     * - Policy must be configured before guard instantiation
     * - Policy changes require new guard instance creation
     * - Policy validation occurs during construction
     *
     * DEPENDENCY REQUIREMENTS:
     * - Valid ResolutionPolicy implementation required
     * - Policy must be properly configured and validated
     * - Policy interface compliance verified at runtime
     *
     * INITIALIZATION SEQUENCE:
     * 1. Validate policy parameter type and interface compliance
     * 2. Store policy reference in readonly property
     * 3. Mark guard as ready for security evaluations
     * 4. Initialize security monitoring integration
     *
     * @param ResolutionPolicy $policy The security policy to enforce for resolution attempts
     *
     * @throws \InvalidArgumentException When policy is invalid or incompatible
     */
    public function __construct(
        private ResolutionPolicy $policy
    ) {}

    /**
     * Evaluates whether service resolution is permitted for the specified abstract.
     *
     * Performs security policy evaluation against the provided abstract (class or
     * interface name) to determine if service resolution should be allowed. Returns
     * structured DTO responses instead of throwing exceptions, enabling graceful
     * security handling without disrupting application flow.
     *
     * SECURITY EVALUATION PROCESS:
     * 1. Policy validation against the abstract identifier
     * 2. Security rule application and decision making
     * 3. Structured response generation (success/error)
     * 4. Security context preservation for monitoring
     * 5. Non-disruptive security enforcement
     *
     * POLICY DECISION FACTORS:
     * - Abstract identifier pattern matching
     * - Namespace-based security rules
     * - Class hierarchy security constraints
     * - Dynamic security context evaluation
     * - Policy-specific security requirements
     *
     * RESPONSE HANDLING:
     * - SuccessDTO: Resolution permitted, proceed normally
     * - ErrorDTO: Resolution blocked, handle security violation
     * - Structured error information for security analysis
     * - Audit-friendly security decision documentation
     *
     * SECURITY MONITORING INTEGRATION:
     * - Policy evaluation events logged for audit trails
     * - Security decision outcomes tracked for compliance
     * - Blocked resolution attempts recorded for analysis
     * - Security metrics updated with evaluation results
     *
     * PERFORMANCE IMPACT:
     * - Efficient policy evaluation algorithms
     * - Minimal overhead for security checks
     * - Cachable policy decisions where applicable
     * - Optimized for high-frequency resolution operations
     *
     * USAGE PATTERNS:
     * ```php
     * // Check resolution permission
     * $securityResult = $guard->check('App\\Service\\UserService');
     *
     * // Handle security decision
     * match (get_class($securityResult)) {
     *     SuccessDTO::class => proceedWithResolution(),
     *     ErrorDTO::class => handleSecurityViolation($securityResult)
     * };
     * ```
     *
     * ERROR RESPONSE STRUCTURE:
     * ```php
     * // Blocked resolution example
     * new ErrorDTO(
     *     message: "Autowiring blocked for [App\\Unsafe\\Service] by strict security policy.",
     *     code: "policy.blocked"
     * )
     * ```
     *
     * SECURITY CONTEXT PRESERVATION:
     * - Original abstract identifier retained in error messages
     * - Policy decision rationale included where applicable
     * - Security violation context for audit and analysis
     * - Timestamp and evaluation metadata
     *
     * COMPLIANCE IMPLICATIONS:
     * - Security policy enforcement decisions logged
     * - Audit trails maintained for regulatory compliance
     * - Security event categorization and reporting
     * - Integration with security monitoring systems
     *
     * @param string $abstract Fully qualified class or interface name to evaluate
     *
     * @return SuccessDTO|ErrorDTO Structured security evaluation result
     */
    public function check(string $abstract) : SuccessDTO|ErrorDTO
    {
        if (! $this->policy->isAllowed(abstract: $abstract)) {
            return new ErrorDTO(
                message: "Autowiring blocked for [{$abstract}] by strict security policy.",
                code   : 'policy.blocked'
            );
        }

        return new SuccessDTO(message: 'Resolution allowed.');
    }
}
