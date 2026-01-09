<?php

declare(strict_types=1);
namespace Avax\Container\Tools\Console;

use Avax\Container\Container;
use Avax\Container\Observe\Inspect\CliPrototypeDumper;
use Throwable;

/**
 * CLI command for detailed inspection of individual container services.
 *
 * This command provides deep insights into a specific service's configuration,
 * lifecycle, dependencies, and construction prototype. It's essential for debugging
 * dependency injection issues and understanding service relationships.
 *
 * INSPECTION SCOPE:
 * - Service registration status and configuration
 * - Caching and instantiation state
 * - Scope classification (singleton, scoped, transient)
 * - Tag associations and groupings
 * - Complete dependency injection prototype
 * - Constructor, property, and method injection details
 *
 * USAGE SCENARIOS:
 * ```bash
 * # Inspect a specific service
 * php bin/container inspect UserRepository
 *
 * # Debug dependency injection issues
 * php bin/container inspect App\\Service\\UserService
 *
 * # Check service configuration
 * php bin/container inspect database.connection
 *
 * # Analyze complex service graphs
 * php bin/container inspect App\\Controller\\UserController
 * ```
 *
 * OUTPUT SECTIONS:
 *
 * 1. SERVICE OVERVIEW:
 *    - Defined: Whether service is registered in container
 *    - Cached: Whether service instance exists in cache
 *    - Scope: Service lifecycle (SINGLETON, SCOPED, TRANSIENT)
 *    - Tags: Associated tag groups for batch operations
 *
 * 2. DEPENDENCY PLAN:
 *    - Complete service resolution blueprint
 *    - Constructor parameters with types and defaults
 *    - Property injection points with #[Inject] attributes
 *    - Method injection calls
 *    - Dependency complexity analysis
 *
 * DEBUGGING WORKFLOW:
 * 1. Run inspect command to identify service configuration
 * 2. Check if service is properly defined and registered
 * 3. Review dependency injection prototype for missing or incorrect dependencies
 * 4. Validate scope settings match intended usage
 * 5. Check tag associations for correct service grouping
 *
 * COMMON ISSUES IDENTIFIED:
 * - Service not registered (undefined)
 * - Incorrect scope causing unexpected behavior
 * - Missing dependencies in injection prototype
 * - Type mismatches in dependency resolution
 * - Tag misconfigurations for service discovery
 *
 * ERROR HANDLING:
 * - Gracefully handles services that cannot be analyzed
 * - Provides clear error messages for debugging
 * - Continues execution even with analysis failures
 * - Suggests alternative approaches for problematic services
 *
 * PERFORMANCE ANALYSIS:
 * - Shows caching status to identify performance issues
 * - Reveals complex dependency graphs
 * - Helps optimize service instantiation patterns
 * - Identifies unnecessary service complexity
 *
 * INTEGRATION WITH DEVELOPMENT:
 * - IDE integration for service inspection
 * - CI/CD pipeline validation
 * - Development debugging workflows
 * - Documentation generation
 * - Code review assistance
 *
 * SECURITY CONSIDERATIONS:
 * - Avoid exposing sensitive service configurations
 * - Consider access controls for production inspection
 * - Filter sensitive information in reports
 * - Audit inspection usage in security reviews
 *
 * ALTERNATIVES:
 * - DiagnoseCommand: System-wide container health
 * - Manual debugging with container methods
 * - IDE debugging with breakpoints
 * - Logging and telemetry analysis
 *
 * @see DiagnoseCommand For system-wide container diagnostics
 * @see CliPrototypeDumper For the prototype visualization logic
 * @see Container::inspect() For the underlying inspection API
 * @see BuildServicePrototype For service prototype generation
 * @see docs_md/Tools/Console/InspectCommand.md#quick-summary
 */
readonly class InspectCommand
{
    /**
     * @param \Avax\Container\Container $container The container instance to inspect
     *
     * @see docs_md/Tools/Console/InspectCommand.md#method-__construct
     */
    public function __construct(
        private Container $container
    ) {}

    /**
     * Execute the inspection command for a specific service.
     *
     * Analyzes the specified service identifier and generates a comprehensive
     * report including registration status, caching state, scope information,
     * tag associations, and complete dependency injection prototype.
     *
     * @param string $abstract The service identifier to inspect
     *
     * @return void Outputs detailed service inspection report to stdout
     * @see docs_md/Tools/Console/InspectCommand.md#method-execute
     */
    public function execute(string $abstract) : void
    {
        echo "\n--- Container Inspect: {$abstract} ---\n";

        $inspector = $this->container->diagnostics()->inspect(id: $abstract);

        echo "Defined:  " . ($inspector['defined'] ? "Yes" : "No") . "\n";
        echo "Cached:   " . ($inspector['cached'] ? "Yes" : "No") . "\n";
        echo "Scope:    " . strtoupper($inspector['scope']) . "\n";

        if (! empty($inspector['tags'])) {
            echo "Tags:     " . implode(', ', $inspector['tags']) . "\n";
        }

        try {
            $prototype = $this->container->getPrototypeFactory()->analyzeReflectionFor(class: $abstract);
            $dumper    = new CliPrototypeDumper();
            echo $dumper->dump(prototype: $prototype);
        } catch (Throwable $throwable) {
            echo "\n[!] Could not generate prototype: {$throwable->getMessage()}\n";
        }

        echo "\n--- End of Report ---\n";
    }
}
