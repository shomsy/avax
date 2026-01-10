<?php

declare(strict_types=1);
namespace Avax\Container\Tools\Console;

use Avax\Container\Container;
use Avax\Container\Observe\Dashboard\DiagnosticsDashboard;

/**
 * CLI command for comprehensive container diagnostics and health checking.
 *
 * This command provides detailed insights into container state, performance metrics,
 * and potential issues. It's designed for development, debugging, and production
 * monitoring of dependency injection health.
 *
 * WHAT IT DIAGNOSES:
 * - Container configuration health (definitions, instances)
 * - Service resolution performance and bottlenecks
 * - Memory usage patterns and potential leaks
 * - Contextual binding rules and scope management
 * - Cache effectiveness and optimization opportunities
 *
 * USAGE SCENARIOS:
 * ```bash
 * # During development
 * php bin/container diagnose
 *
 * # In production monitoring
 * php artisan container:diagnose > container_health.log
 *
 * # Automated health checks
 * php bin/container diagnose | grep -E "(ERROR|WARNING|SLOW)"
 * ```
 *
 * OUTPUT SECTIONS:
 *
 * 1. GENERAL STATS:
 *    - Total service definitions registered
 *    - Number of cached service instances
 *    - Circular dependency depth limit
 *    - Number of contextual binding rules
 *
 * 2. RESOLUTION TIMELINE:
 *    - Service resolution performance metrics
 *    - Slow resolution warnings (>1ms threshold)
 *    - Memory usage per service resolution
 *    - Bottleneck identification
 *
 * 3. MEMORY SNAPSHOTS:
 *    - Peak memory usage during resolution
 *    - Memory delta per service
 *    - Potential memory leak indicators
 *    - Cache memory efficiency metrics
 *
 * INTERPRETING RESULTS:
 *
 * HEALTHY INDICATORS:
 * - Definitions count matches expected services
 * - Instances count reasonable for application size
 * - No services taking >10ms to resolve
 * - Memory deltas consistent and reasonable
 *
 * WARNING SIGNS:
 * - High instances count may indicate over-caching
 * - Slow resolutions suggest complex dependency graphs
 * - Large memory deltas indicate heavy service initialization
 * - Missing definitions for expected services
 *
 * DEBUGGING WORKFLOW:
 * 1. Run diagnose command to identify issues
 * 2. Check slow services with detailed timeline
 * 3. Review memory usage for optimization opportunities
 * 4. Validate contextual bindings are working correctly
 * 5. Ensure proper service scoping and lifecycle
 *
 * INTEGRATION:
 * Can be integrated into:
 * - Development workflows (pre-commit hooks)
 * - CI/CD pipelines (automated health checks)
 * - Production monitoring (periodic health reports)
 * - Debugging tools (container inspection)
 *
 * PERFORMANCE IMPACT:
 * - Diagnostic collection adds minimal overhead
 * - Timeline tracking can be disabled in production
 * - Memory snapshots are optional and configurable
 *
 * SECURITY CONSIDERATIONS:
 * - Output may contain sensitive configuration details
 * - Consider access controls for production diagnostics
 * - Filter sensitive data in automated reports
 *
 * @see DiagnosticsDashboard For the underlying diagnostics system
 * @see Container::diagnostics() For diagnostics API
 * @see Container::inspect() For container inspection
 * @see docs/Tools/Console/DiagnoseCommand.md#quick-summary
 */
readonly class DiagnoseCommand
{
    /**
     * @param \Avax\Container\Container $container The container instance to diagnose
     *
     * @see docs/Tools/Console/DiagnoseCommand.md#method-__construct
     */
    public function __construct(
        private Container $container
    ) {}

    /**
     * Execute the diagnostic command and display comprehensive health report.
     *
     * This method runs a full container health check and outputs a formatted
     * report to stdout. The report includes ASCII art branding, statistics,
     * performance metrics, and actionable insights.
     *
     * OUTPUT FORMAT:
     * The command produces human-readable output with clear sections:
     * - ASCII art header for visual identification
     * - Statistical overview of container state
     * - Performance timeline with bottleneck identification
     * - Memory usage analysis
     * - Summary and recommendations
     *
     * @return void Outputs diagnostic report to stdout
     * @see docs/Tools/Console/DiagnoseCommand.md#method-execute
     */
    public function execute() : void
    {
        echo "\n      _      __  __  __ ";
        echo "\n     /_\    \ \/ / /__\\";
        echo "\n    //_\\\\    \  /  /_\  ";
        echo "\n   /  _  \\   /  \ //__  ";
        echo "\n  /_/   \_\ /_/\_\\\\__/  ";
        echo "\n  --- Container Diagnose ---\n\n";

        $inspector = $this->container->diagnostics()->inspect();
        $stats     = $inspector->getStats();

        echo "--- General Stats ---\n";
        echo sprintf('Definitions: %s%s', $stats['definitions_count'], PHP_EOL);
        echo sprintf('Instances (Cached): %s%s', $stats['instances_count'], PHP_EOL);
        echo "Circular Depth Limit: 50\n";
        echo "Lifecycle Scopes: " . ($this->container->getDefinitionStore()->getAllContextual() !== [] ? count($this->container->getDefinitionStore()->getAllContextual()) : 0) . " (Contextual Rules)\n";

        echo "\n--- Resolution Timeline ---\n";
        $dashboard = new DiagnosticsDashboard(
            timeline: $this->container->getDiagnostics(),
            metrics : $this->container->metrics()
        );

        echo $dashboard->generateCliReport();

        echo "\n--- Memory Snapshots ---\n";
        $snapshot = $this->container->metrics()?->getSnapshot() ?? [];
        foreach ($snapshot as $key => $value) {
            echo str_pad($key . ":", 25) . $value . "\n";
        }

        echo "\n--- End of Report ---\n";
    }
}
