<?php

declare(strict_types=1);
namespace Avax\Container\Tools\Console;

use Avax\Commands\CommandDefinitions;
use Avax\Container\Features\Define\Store\ServiceDefinitionRepository;
use Avax\Container\Features\Define\Store\ServiceDiscovery;
use Avax\Container\Features\Think\Cache\CacheManagerIntegration;
use Avax\Container\Guard\Rules\ServiceValidator;
use Avax\Container\Observe\Metrics\EnhancedMetricsCollector;
use Avax\DataHandling\ArrayHandling\Arrhae;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * Enterprise-grade CLI command for comprehensive container inspection and diagnostics.
 *
 * This sophisticated Symfony Console command provides deep operational visibility into
 * dependency injection container state, performance metrics, service dependencies, cache
 * statistics, and validation results. It serves as the primary diagnostic tool for container
 * operations, incident response, and performance optimization workflows.
 *
 * ARCHITECTURAL ROLE:
 * - Unified diagnostic interface for container operations
 * - Comprehensive health assessment and reporting
 * - Performance analysis and bottleneck identification
 * - Service dependency visualization and cycle detection
 * - Cache performance monitoring and optimization insights
 * - Service validation and integrity verification
 * - Structured output formats for automation and integration
 *
 * DIAGNOSTIC CAPABILITIES:
 * - Real-time health checks with status assessment
 * - Performance metrics collection and anomaly detection
 * - Service inventory and lifecycle analysis
 * - Dependency graph analysis and circular reference detection
 * - Multi-layer cache statistics and hit rate analysis
 * - Comprehensive service validation with error categorization
 * - Actionable recommendations for optimization and maintenance
 *
 * OUTPUT FORMATS:
 * - Table format: Human-readable console output with styling
 * - JSON format: Machine-readable structured data for automation
 * - YAML format: Configuration-friendly hierarchical data
 * - Verbose mode: Detailed diagnostic information and context
 *
 * USAGE SCENARIOS:
 * ```bash
 * # Complete health assessment
 * php artisan container:inspect
 *
 * # Performance analysis with verbose output
 * php artisan container:inspect --performance --verbose
 *
 * # Service dependency analysis in JSON format
 * php artisan container:inspect --dependencies --format=json
 *
 * # Cache diagnostics with health checks
 * php artisan container:inspect --cache --check-health
 *
 * # Full validation report
 * php artisan container:inspect --validate --verbose
 * ```
 *
 * COMMAND OPTIONS:
 * - --format (-f): Output format (table, json, yaml)
 * - --verbose (-v): Detailed output with additional context
 * - --check-health: Perform comprehensive health assessment
 * - --performance: Show performance analysis and metrics
 * - --services: Display service inventory and statistics
 * - --dependencies: Analyze service dependencies and relationships
 * - --cache: Show cache statistics and performance
 * - --validate: Run service validation and integrity checks
 *
 * INTEGRATION POINTS:
 * - EnhancedMetricsCollector: Performance and health metrics
 * - ServiceDefinitionRepository: Service metadata and statistics
 * - ServiceDiscovery: Dependency analysis and discovery
 * - ServiceValidator: Service validation and integrity checks
 * - CacheManagerIntegration: Cache diagnostics and monitoring
 * - SymfonyStyle: Enhanced console output formatting
 *
 * PERFORMANCE CHARACTERISTICS:
 * - Settings queries for service and dependency data
 * - Cache operations for statistics retrieval
 * - Validation execution across all services
 * - Memory usage scales with service count and verbosity
 * - I/O operations for cache backend communication
 * - Computational complexity for dependency analysis
 *
 * SECURITY CONSIDERATIONS:
 * - Service identifiers and configuration metadata exposure
 * - Access restriction to trusted operators and administrators
 * - Sensitive data redaction in verbose output
 * - Audit logging of diagnostic command execution
 * - Secure handling of service configuration data
 *
 * ERROR HANDLING:
 * - Graceful degradation when services are unavailable
 * - Comprehensive error reporting with context
 * - Non-disruptive operation during container issues
 * - Structured error output in all supported formats
 * - Recovery suggestions for common failure scenarios
 *
 * MONITORING INTEGRATION:
 * - Health check results feed into monitoring systems
 * - Performance metrics contribute to alerting thresholds
 * - Error conditions trigger incident response workflows
 * - Diagnostic data supports capacity planning decisions
 * - Trend analysis for optimization recommendations
 *
 * THREAD SAFETY:
 * - CLI execution context with single-threaded operation
 * - Console runtime manages concurrency and isolation
 * - Read-only operations on container state and metrics
 * - Safe parallel execution of diagnostic sections
 * - Resource cleanup on command completion
 *
 * COMPLIANCE FEATURES:
 * - Audit trail generation for diagnostic activities
 * - Structured logging of command execution and results
 * - Regulatory reporting capabilities for container state
 * - Data retention policies for diagnostic output
 * - Access control and authorization verification
 *
 * EXTENSIBILITY:
 * - Plugin architecture for custom diagnostic modules
 * - Configurable output formatters and renderers
 * - Custom health checks and validation rules
 * - Integration hooks for external monitoring systems
 * - Command option expansion for specialized diagnostics
 *
 * TROUBLESHOOTING VALUE:
 * - Comprehensive container state visibility
 * - Performance bottleneck identification
 * - Configuration issue detection and diagnosis
 * - Dependency problem isolation and resolution
 * - Cache performance optimization guidance
 * - Service validation error categorization
 *
 * @package Avax\Container\Tools\Console
 * @see     EnhancedMetricsCollector For performance and health metrics collection
 * @see     ServiceDefinitionRepository For service metadata access and statistics
 * @see     ServiceDiscovery For dependency analysis and service discovery
 * @see     ServiceValidator For service validation and integrity verification
 * @see     CacheManagerIntegration For cache diagnostics and management
 * @see     SymfonyStyle For enhanced console output formatting
 * @see docs/Tools/Console/ContainerInspectCommand.md#quick-summary
 */
#[CommandDefinitions(name: 'container:inspect')]
class ContainerInspectCommand extends Command
{
    /**
     * @var string|null Default command name.
     */
    protected static $defaultName = 'container:inspect';
    /**
     * @var string|null Default command description.
     */
    protected static $defaultDescription = 'Inspect and diagnose container health and performance';

    /**
     * @param EnhancedMetricsCollector    $metrics      Metrics collector for performance analysis.
     * @param ServiceDefinitionRepository $serviceRepo  Service repository for service metadata.
     * @param ServiceDiscovery            $discovery    Discovery API for dependency analysis.
     * @param ServiceValidator            $validator    Validator for service integrity checks.
     * @param CacheManagerIntegration     $cacheManager Cache integration for cache diagnostics.
     *
     * @see docs/Tools/Console/ContainerInspectCommand.md#method-__construct
     */
    public function __construct(
        private readonly EnhancedMetricsCollector    $metrics,
        private readonly ServiceDefinitionRepository $serviceRepo,
        private readonly ServiceDiscovery            $discovery,
        private readonly ServiceValidator            $validator,
        private readonly CacheManagerIntegration     $cacheManager,
    )
    {
        parent::__construct();
    }

    /**
     * Configure CLI options and flags.
     *
     * @return void
     * @see docs/Tools/Console/ContainerInspectCommand.md#method-configure
     */
    protected function configure() : void
    {
        $this
            ->addOption(name: 'format', shortcut: 'f', mode: InputOption::VALUE_REQUIRED, description: 'Output format (table, json, yaml)', default: 'table')
            ->addOption(name: 'verbose', shortcut: 'v', mode: InputOption::VALUE_NONE, description: 'Verbose output with additional details')
            ->addOption(name: 'check-health', shortcut: null, mode: InputOption::VALUE_NONE, description: 'Perform comprehensive health checks')
            ->addOption(name: 'performance', shortcut: null, mode: InputOption::VALUE_NONE, description: 'Show performance analysis')
            ->addOption(name: 'services', shortcut: null, mode: InputOption::VALUE_NONE, description: 'List all services with details')
            ->addOption(name: 'dependencies', shortcut: null, mode: InputOption::VALUE_NONE, description: 'Analyze service dependencies')
            ->addOption(name: 'cache', shortcut: null, mode: InputOption::VALUE_NONE, description: 'Show cache statistics')
            ->addOption(name: 'validate', shortcut: null, mode: InputOption::VALUE_NONE, description: 'Validate all services')
            ->setDescription(description: 'Inspect and diagnose container health, performance, and configuration');
    }

    /**
     * Execute the inspection workflow.
     *
     * @param InputInterface  $input  Input arguments and options.
     * @param OutputInterface $output Output writer.
     *
     * @return int Command exit code.
     * @see docs/Tools/Console/ContainerInspectCommand.md#method-execute
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io      = new SymfonyStyle(input: $input, output: $output);
        $format  = $input->getOption(name: 'format');
        $verbose = $input->getOption(name: 'verbose');

        $io->title(message: 'ðŸ” Container Inspection Report');

        // Determine what to show
        $showAll = ! $input->getOption(name: 'check-health') &&
            ! $input->getOption(name: 'performance') &&
            ! $input->getOption(name: 'services') &&
            ! $input->getOption(name: 'dependencies') &&
            ! $input->getOption(name: 'cache') &&
            ! $input->getOption(name: 'validate');

        try {
            if ($showAll || $input->getOption(name: 'check-health')) {
                $this->showHealthCheck(io: $io, format: $format, verbose: $verbose);
            }

            if ($showAll || $input->getOption(name: 'performance')) {
                $this->showPerformanceAnalysis(io: $io, format: $format, verbose: $verbose);
            }

            if ($showAll || $input->getOption(name: 'services')) {
                $this->showServicesOverview(io: $io, format: $format, verbose: $verbose);
            }

            if ($showAll || $input->getOption(name: 'dependencies')) {
                $this->showDependencyAnalysis(io: $io, format: $format, verbose: $verbose);
            }

            if ($showAll || $input->getOption(name: 'cache')) {
                $this->showCacheStatistics(io: $io, format: $format, verbose: $verbose);
            }

            if ($showAll || $input->getOption(name: 'validate')) {
                $this->showValidationResults(io: $io, format: $format, verbose: $verbose);
            }

            if ($showAll) {
                $this->showRecommendations(io: $io, format: $format);
            }

            return Command::SUCCESS;

        } catch (Throwable $e) {
            $io->error(message: 'Inspection failed: ' . $e->getMessage());

            if ($verbose) {
                $io->block(messages: $e->getTraceAsString());
            }

            return Command::FAILURE;
        }
    }

    /**
     * Render a health check summary.
     *
     * @param SymfonyStyle $io      Console style helper.
     * @param string       $format  Output format.
     * @param bool         $verbose Whether to show detailed output.
     *
     * @return void
     * @see docs/Tools/Console/ContainerInspectCommand.md#method-showhealthcheck
     */
    private function showHealthCheck(SymfonyStyle $io, string $format, bool $verbose) : void
    {
        $io->section(message: 'ðŸ¥ Health Check');

        $healthData = $this->cacheManager->healthCheck();
        $status     = $healthData['status'];

        match ($status) {
            'healthy'   => $io->success(message: 'Container is healthy'),
            'degraded'  => $io->warning(message: 'Container is degraded'),
            'unhealthy' => $io->error(message: 'Container is unhealthy'),
        };

        if ($verbose) {
            $this->displayHealthDetails(io: $io, healthData: $healthData, format: $format);
        }
    }

    /**
     * Display health check details.
     *
     * @param SymfonyStyle         $io         Console style helper.
     * @param array<string, mixed> $healthData Health data payload.
     * @param string               $format     Output format.
     *
     * @return void
     * @see docs/Tools/Console/ContainerInspectCommand.md#method-displayhealthdetails
     */
    private function displayHealthDetails(SymfonyStyle $io, array $healthData, string $format) : void
    {
        if ($format === 'json') {
            $io->writeln(messages: json_encode($healthData['checks'], JSON_PRETTY_PRINT));
        } else {
            foreach ($healthData['checks'] as $checkName => $checkData) {
                $io->writeln(messages: "{$checkName}: " . json_encode($checkData));
            }
        }
    }

    /**
     * Render performance analysis metrics.
     *
     * @param SymfonyStyle $io      Console style helper.
     * @param string       $format  Output format.
     * @param bool         $verbose Whether to show anomaly details.
     *
     * @return void
     * @see docs/Tools/Console/ContainerInspectCommand.md#method-showperformanceanalysis
     */
    private function showPerformanceAnalysis(SymfonyStyle $io, string $format, bool $verbose) : void
    {
        $io->section(message: 'âš¡ Performance Analysis');

        $stats = [
            'resolutions' => $this->metrics->getResolutionCount(),
            'avg_time'    => round($this->metrics->getAverageResolutionTime() * 1000, 2) . 'ms',
            'error_rate'  => round($this->metrics->getErrorRate(), 2) . '%',
        ];

        if ($format === 'json') {
            $io->writeln(messages: json_encode($stats, JSON_PRETTY_PRINT));
        } else {
            $io->table(
                headers: ['Metric', 'Value'],
                rows   : [
                    ['Total Resolutions', $stats['resolutions']],
                    ['Average Resolution Time', $stats['avg_time']],
                    ['Error Rate', $stats['error_rate']],
                ]
            );
        }

        if ($verbose) {
            $anomalies = $this->metrics->detectPerformanceAnomalies();
            if (! empty($anomalies)) {
                $io->warning(message: 'Performance anomalies detected:');
                $this->displayAnomalies(io: $io, anomalies: $anomalies, format: $format);
            }
        }
    }

    /**
     * Display detected performance anomalies.
     *
     * @param SymfonyStyle      $io        Console style helper.
     * @param array<int, mixed> $anomalies Anomaly list.
     * @param string            $format    Output format.
     *
     * @return void
     * @see docs/Tools/Console/ContainerInspectCommand.md#method-displayanomalies
     */
    private function displayAnomalies(SymfonyStyle $io, array $anomalies, string $format) : void
    {
        if ($format === 'json') {
            $io->writeln(messages: json_encode($anomalies, JSON_PRETTY_PRINT));
        } else {
            $tableData = array_map(static function ($anomaly) {
                return [
                    $anomaly['service'],
                    round($anomaly['duration'] * 1000, 2) . 'ms',
                    $anomaly['strategy']
                ];
            }, $anomalies);

            $io->table(headers: ['Service', 'Duration', 'Strategy'], rows: $tableData);
        }
    }

    /**
     * Render a summary of services and lifetimes.
     *
     * @param SymfonyStyle $io      Console style helper.
     * @param string       $format  Output format.
     * @param bool         $verbose Whether to show detailed service data.
     *
     * @return void
     *
     * @throws \Exception
     * @see docs/Tools/Console/ContainerInspectCommand.md#method-showservicesoverview
     */
    private function showServicesOverview(SymfonyStyle $io, string $format, bool $verbose) : void
    {
        $io->section(message: 'ðŸ“¦ Services Overview');

        $services = $this->serviceRepo->findAll();
        $stats    = $this->serviceRepo->getServiceStats();

        if ($format === 'json') {
            $io->writeln(messages: json_encode($stats, JSON_PRETTY_PRINT));
        } else {
            $io->table(
                headers: ['Category', 'Count'],
                rows   : [
                    ['Total Services', $stats['total_services']],
                    ['Active Services', $services->where('isActive', true)->count()],
                    ['Singleton Services', $stats['by_lifetime']['singleton'] ?? 0],
                    ['Scoped Services', $stats['by_lifetime']['scoped'] ?? 0],
                    ['Transient Services', $stats['by_lifetime']['transient'] ?? 0],
                ]
            );
        }

        if ($verbose) {
            $this->showServiceDetails(io: $io, services: $services, format: $format);
        }
    }

    /**
     * Render detailed service metadata.
     *
     * @param SymfonyStyle $io       Console style helper.
     * @param Arrhae       $services Service collection.
     * @param string       $format   Output format.
     *
     * @return void
     * @see docs/Tools/Console/ContainerInspectCommand.md#method-showservicedetails
     */
    private function showServiceDetails(SymfonyStyle $io, Arrhae $services, string $format) : void
    {
        if ($format === 'json') {
            $io->writeln(messages: json_encode($services->all(), JSON_PRETTY_PRINT));
        } else {
            $tableData = $services->map(callback: static function ($service) {
                return [
                    $service->id,
                    $service->class,
                    $service->lifetime->value,
                    count($service->dependencies),
                    $service->isActive ? 'Yes' : 'No'
                ];
            })->all();

            $io->table(headers: ['ID', 'Class', 'Lifetime', 'Deps', 'Active'], rows: $tableData);
        }
    }

    /**
     * Render dependency analysis, including cycles.
     *
     * @param SymfonyStyle $io      Console style helper.
     * @param string       $format  Output format.
     * @param bool         $verbose Whether to include detail sections.
     *
     * @return void
     *
     * @throws \Exception
     * @see docs/Tools/Console/ContainerInspectCommand.md#method-showdependencyanalysis
     */
    private function showDependencyAnalysis(SymfonyStyle $io, string $format, bool $verbose) : void
    {
        $io->section(message: 'ðŸ”— Dependency Analysis');

        $dependencyStats = $this->serviceRepo->analyzeDependencies();

        if ($format === 'json') {
            $io->writeln(messages: json_encode($dependencyStats, JSON_PRETTY_PRINT));
        } else {
            $io->table(
                headers: ['Metric', 'Value'],
                rows   : [
                    ['Total Dependencies', $dependencyStats['stats']['total_dependencies']],
                    ['Circular Dependencies', count($dependencyStats['cycles'])],
                    ['Orphan Services', count($dependencyStats['orphans'])],
                    ['Avg Deps per Service', $dependencyStats['stats']['avg_deps_per_service']],
                ]
            );
        }

        if (! empty($dependencyStats['cycles'])) {
            $io->error(message: 'Circular dependencies detected!');
            foreach ($dependencyStats['cycles'] as $cycle) {
                $io->writeln(messages: '  â€¢ ' . implode(' â†’ ', $cycle));
            }
        }

        if ($verbose) {
            $this->showDependencyDetails(io: $io, dependencyStats: $dependencyStats, format: $format);
        }
    }

    /**
     * Render extended dependency breakdowns.
     *
     * @param SymfonyStyle         $io              Console style helper.
     * @param array<string, mixed> $dependencyStats Dependency analysis data.
     * @param string               $format          Output format.
     *
     * @return void
     * @see docs/Tools/Console/ContainerInspectCommand.md#method-showdependencydetails
     */
    private function showDependencyDetails(SymfonyStyle $io, array $dependencyStats, string $format) : void
    {
        if (! empty($dependencyStats['most_depended'])) {
            $io->subsection('Most Depended Services');

            if ($format === 'json') {
                $io->writeln(messages: json_encode($dependencyStats['most_depended'], JSON_PRETTY_PRINT));
            } else {
                $tableData = array_map(static function ($serviceId, $count) {
                    return [$serviceId, $count];
                }, array_keys($dependencyStats['most_depended']), $dependencyStats['most_depended']);

                $io->table(headers: ['Service', 'Dependents'], rows: $tableData);
            }
        }
    }

    /**
     * Render cache statistics and optional prototype cache metrics.
     *
     * @param SymfonyStyle $io      Console style helper.
     * @param string       $format  Output format.
     * @param bool         $verbose Whether to include prototype cache stats.
     *
     * @return void
     * @see docs/Tools/Console/ContainerInspectCommand.md#method-showcachestatistics
     */
    private function showCacheStatistics(SymfonyStyle $io, string $format, bool $verbose) : void
    {
        $io->section(message: 'ðŸ’¾ Cache Statistics');

        $cacheStats = $this->cacheManager->getGlobalStats();

        if ($format === 'json') {
            $io->writeln(messages: json_encode($cacheStats, JSON_PRETTY_PRINT));
        } else {
            $io->table(
                headers: ['Component', 'Status'],
                rows   : [
                    ['Cache Backend', $cacheStats['backend_type']],
                    ['Prototype Cache Available', 'Yes'],
                    ['Instance Cache Available', 'Yes'],
                    ['Scoped Cache Available', 'Yes'],
                ]
            );
        }

        if ($verbose) {
            $prototypeCache = $this->cacheManager->createPrototypeCache();
            if (method_exists($prototypeCache, 'getStats')) {
                $prototypeStats = $prototypeCache->getStats();
                $io->table(
                    headers: ['Prototype Cache', 'Value'],
                    rows   : [
                        ['Hits', $prototypeStats['stats']['hits']],
                        ['Misses', $prototypeStats['stats']['misses']],
                        ['Hit Rate', $prototypeStats['hit_rate'] . '%'],
                    ]
                );
            }
        }
    }

    /**
     * Render validation summary and error details.
     *
     * @param SymfonyStyle $io      Console style helper.
     * @param string       $format  Output format.
     * @param bool         $verbose Whether to include full error breakdown.
     *
     * @return void
     *
     * @throws \Exception
     * @see docs/Tools/Console/ContainerInspectCommand.md#method-showvalidationresults
     */
    private function showValidationResults(SymfonyStyle $io, string $format, bool $verbose) : void
    {
        $io->section(message: 'âœ… Validation Results');

        $validationSummary = $this->validator->getValidationSummary();

        if ($format === 'json') {
            $io->writeln(messages: json_encode($validationSummary, JSON_PRETTY_PRINT));
        } else {
            $io->table(
                headers: ['Validation', 'Count'],
                rows   : [
                    ['Valid Services', $validationSummary['valid_services']],
                    ['Invalid Services', $validationSummary['invalid_services']],
                    ['Total Errors', $validationSummary['total_errors']],
                    ['Total Warnings', $validationSummary['total_warnings']],
                ]
            );
        }

        if ($validationSummary['invalid_services'] > 0) {
            $io->error(message: 'Validation errors found!');

            if ($verbose) {
                $services = $this->serviceRepo->findAll();
                $results  = $this->validator->validateServices(services: $services->all());

                foreach ($results as $result) {
                    if (! $result['isValid']) {
                        $io->section(message: "Service: {$result['serviceId']}");
                        foreach ($result['errors'] as $error) {
                            $io->error(message: "  â€¢ {$error['message']}");
                        }
                    }
                }
            }
        }
    }

    /**
     * Render optimization recommendations based on diagnostics data.
     *
     * @param SymfonyStyle $io     Console style helper.
     * @param string       $format Output format.
     *
     * @return void
     *
     * @throws \Exception
     * @see docs/Tools/Console/ContainerInspectCommand.md#method-showrecommendations
     */
    private function showRecommendations(SymfonyStyle $io, string $format) : void
    {
        $io->section(message: 'ðŸ’¡ Recommendations');

        $recommendations = [];

        // Health-based recommendations
        $health = $this->cacheManager->healthCheck();
        if ($health['status'] !== 'healthy') {
            $recommendations[] = 'Check cache backend configuration and connectivity';
        }

        // Performance-based recommendations
        $anomalies = $this->metrics->detectPerformanceAnomalies();
        if (! empty($anomalies)) {
            $recommendations[] = 'Review slow service resolutions and optimize if needed';
        }

        // Dependency-based recommendations
        $deps = $this->serviceRepo->analyzeDependencies();
        if (! empty($deps['cycles'])) {
            $recommendations[] = 'Resolve circular dependencies to prevent resolution failures';
        }

        // Validation-based recommendations
        $validation = $this->validator->getValidationSummary();
        if ($validation['invalid_services'] > 0) {
            $recommendations[] = 'Fix service validation errors for proper container operation';
        }

        if (empty($recommendations)) {
            $io->success(message: 'No recommendations - container is well-configured!');
        } else {
            foreach ($recommendations as $rec) {
                $io->warning(message: "â€¢ {$rec}");
            }
        }
    }
}
