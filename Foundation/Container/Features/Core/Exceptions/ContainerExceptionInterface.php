<?php

declare(strict_types=1);
namespace Avax\Container\Features\Core\Exceptions;

use Throwable;

/**
 * @package Avax\Container\Core\Exceptions
 *
 * Unified exception interface for all container-related errors.
 *
 * ContainerExceptionInterface provides a common contract for all exceptions thrown
 * by the dependency injection container. This enables consistent error handling,
 * logging, and debugging across all container operations.
 *
 * WHY IT EXISTS:
 * - To provide a unified error handling contract across the container ecosystem
 * - To enable PSR-11 compatible error handling for external integrations
 * - To support consistent exception chaining and context preservation
 * - To facilitate debugging and error reporting in complex dependency graphs
 *
 * EXCEPTION HIERARCHY:
 * ContainerExceptionInterface (base)
 * â”œâ”€â”€ ResolutionException (service resolution failures)
 * â”œâ”€â”€ InjectionException (dependency injection failures)
 * â”œâ”€â”€ CircularDependencyException (circular reference detection)
 * â”œâ”€â”€ DefinitionException (service definition errors)
 * â”œâ”€â”€ PolicyException (security/policy violations)
 * â””â”€â”€ ValidationException (prototype/service validation errors)
 *
 * USAGE PATTERNS:
 * ```php
 * try {
 *     $service = $container->get('MyService');
 * } catch (ContainerExceptionInterface $e) {
 *     // Handle all container errors uniformly
 *     $this->logger->error('Container error', [
 *         'service' => 'MyService',
 *         'error' => $e->getMessage(),
 *         'context' => $e->getContext()
 *     ]);
 * }
 * ```
 *
 * CONTEXT PRESERVATION:
 * All container exceptions should preserve resolution context, dependency chains,
 * and diagnostic information to aid in debugging complex injection scenarios.
 *
 * THREAD SAFETY:
 * Exception instances are immutable and safe to share across threads.
 *
 * @see     ResolutionException For service resolution failures
 * @see     InjectionException For dependency injection failures
 * @see     CircularDependencyException For circular reference detection
 */
interface ContainerExceptionInterface extends Throwable
{
    /**
     * Returns the service identifier that triggered this exception.
     *
     * @return string|null The service identifier or null if not applicable
     */
    public function getServiceId() : string|null;

    /**
     * Returns additional context information about the error.
     *
     * Context may include dependency chain, resolution path, validation details,
     * or other diagnostic information relevant to debugging the issue.
     *
     * @return array Additional context information
     */
    public function getContext() : array;
}