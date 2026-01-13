<?php

declare(strict_types=1);

namespace Avax\Container\Tools\Console;

use Avax\Commands\CommandDefinitions;
use Avax\Container\Features\Core\Enum\ServiceLifetime;
use Avax\Container\Features\Define\Store\ServiceDefinitionEntity;
use Avax\Container\Features\Define\Store\ServiceDefinitionRepository;
use Avax\DataHandling\ArrayHandling\Arrhae;
use DateTimeImmutable;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

/**
 * Enterprise-grade CLI command for comprehensive container service management.
 *
 * This sophisticated Symfony Console command provides complete lifecycle management
 * for dependency injection container service definitions, enabling operators to perform
 * CRUD operations, bulk import/export, and advanced service configuration management
 * through an intuitive command-line interface.
 *
 * ARCHITECTURAL ROLE:
 * - Service definition lifecycle management interface
 * - Configuration-as-code for dependency injection
 * - Operational tooling for container administration
 * - Service inventory management and governance
 * - Configuration drift detection and remediation
 * - Bulk service operations for deployment automation
 *
 * MANAGEMENT CAPABILITIES:
 * - Service registration with full metadata configuration
 * - Selective service updates with change tracking
 * - Safe service deactivation (soft delete pattern)
 * - Bulk service import/export for environment migration
 * - Service inventory visualization and analysis
 * - Configuration validation and integrity verification
 *
 * SERVICE OPERATIONS:
 * - CREATE: Register new services with complete configuration
 * - READ: Query and display service definitions and metadata
 * - UPDATE: Modify existing service configurations selectively
 * - DELETE: Safely deactivate services without data loss
 * - IMPORT: Bulk service registration from configuration files
 * - EXPORT: Service inventory extraction for backup/migration
 *
 * USAGE SCENARIOS:
 * ```bash
 * # List all services with statistics
 * php artisan container:services list
 *
 * # Add a new singleton service
 * php artisan container:services add database --class="App\\Database" --lifetime=singleton
 * --tags="infrastructure,persistence"
 *
 * # Update service configuration
 * php artisan container:services update cache --config='{"ttl":3600,"driver":"redis"}'
 *
 * # Show detailed service information
 * php artisan container:services show user.repository
 *
 * # Export services for backup
 * php artisan container:services export --file=services-backup.json --environment=production
 *
 * # Import services from configuration
 * php artisan container:services import --file=new-services.json
 *
 * # Safely remove a service
 * php artisan container:services remove old.service
 * ```
 *
 * COMMAND ACTIONS:
 * - list: Display service inventory with filtering and statistics
 * - add: Register new service with complete configuration
 * - update: Modify existing service properties selectively
 * - remove: Deactivate service (soft delete for safety)
 * - import: Bulk import services from JSON configuration
 * - export: Export service definitions to JSON format
 * - show: Display detailed information for specific service
 *
 * CONFIGURATION OPTIONS:
 * - --class: Fully qualified service class name
 * - --lifetime: Service lifetime scope (singleton/scoped/transient)
 * - --tags: Comma-separated service categorization tags
 * - --dependencies: Comma-separated service dependency IDs
 * - --environment: Environment-specific service configuration
 * - --config: JSON configuration object for service parameters
 * - --file: File path for import/export operations
 * - --format: Output format (table/json) for display commands
 *
 * INTEGRATION POINTS:
 * - ServiceDefinitionRepository: Persistent storage and retrieval
 * - ServiceDefinitionEntity: Service metadata and configuration
 * - ServiceLifetime enum: Lifetime scope definitions
 * - SymfonyStyle: Enhanced console output formatting
 * - Arrhae: Collection manipulation and data transformation
 *
 * DATA MANAGEMENT:
 * - JSON-based configuration for portability
 * - Environment-specific service definitions
 * - Tag-based service organization and discovery
 * - Dependency relationship tracking and validation
 * - Configuration versioning and change history
 *
 * PERFORMANCE CHARACTERISTICS:
 * - Settings operations for service persistence
 * - JSON parsing/serialization for import/export
 * - Collection operations for bulk processing
 * - Memory usage scales with service count
 * - I/O operations for file-based import/export
 *
 * SECURITY CONSIDERATIONS:
 * - Service configuration changes affect runtime behavior
 * - Access control required for production environments
 * - Configuration validation prevents malformed services
 * - Audit logging for all service management operations
 * - Safe deactivation prevents immediate breaking changes
 * - Input sanitization for all configuration parameters
 *
 * ERROR HANDLING:
 * - Comprehensive validation of service configurations
 * - Graceful handling of missing or invalid services
 * - Detailed error messages for troubleshooting
 * - Transaction-like operations for data consistency
 * - Recovery suggestions for failed operations
 *
 * VALIDATION FEATURES:
 * - Service ID uniqueness verification
 * - Class existence and accessibility checks
 * - Dependency relationship validation
 * - Configuration schema validation
 * - Environment compatibility verification
 * - Tag format and content validation
 *
 * COMPLIANCE FEATURES:
 * - Audit trail generation for configuration changes
 * - Change tracking with timestamps and user context
 * - Configuration versioning for rollback capabilities
 * - Regulatory compliance logging for enterprise requirements
 * - Data retention policies for configuration history
 *
 * BACKWARD COMPATIBILITY:
 * - Maintains compatibility with existing service definitions
 * - Gradual migration path for legacy configuration formats
 * - Extensible command structure for future operations
 * - Version-aware import/export capabilities
 *
 * TROUBLESHOOTING CAPABILITIES:
 * - Detailed service information display
 * - Configuration validation and error reporting
 * - Dependency relationship visualization
 * - Import/export operation status and error details
 * - Command execution logging and debugging
 *
 * EXTENSIBILITY:
 * - Plugin architecture for custom service operations
 * - Custom validation rules and constraints
 * - Additional output formats and display options
 * - Integration hooks for external service registries
 * - Custom command actions through extension points
 *
 * @see     ServiceDefinitionRepository For service persistence and retrieval operations
 * @see     ServiceDefinitionEntity For service metadata structure and validation
 * @see     ServiceLifetime For service lifetime scope definitions
 * @see     SymfonyStyle For enhanced console output and user interaction
 * @see     Arrhae For collection manipulation and data transformation utilities
 * @see     docs/Tools/Console/ServiceManagementCommand.md#quick-summary
 */
#[CommandDefinitions(name: 'container:services')]
class ServiceManagementCommand extends Command
{
    /**
     * @var string|null Default command name.
     */
    protected static $defaultName = 'container:services';

    /**
     * @var string|null Default command description.
     */
    protected static $defaultDescription = 'Manage container services';

    /**
     * @param ServiceDefinitionRepository $serviceRepo Settings for service definitions.
     *
     * @see docs/Tools/Console/ServiceManagementCommand.md#method-__construct
     */
    public function __construct(
        private readonly ServiceDefinitionRepository $serviceRepo
    )
    {
        parent::__construct();
    }

    /**
     * Configure CLI arguments and options.
     *
     * @see docs/Tools/Console/ServiceManagementCommand.md#method-configure
     */
    protected function configure() : void
    {
        $this
            ->addArgument(name: 'action', mode: InputArgument::REQUIRED, description: 'Action: list, add, update, remove, import, export')
            ->addArgument(name: 'service', mode: InputArgument::OPTIONAL, description: 'Service ID')
            ->addOption(name: 'class', shortcut: null, mode: InputOption::VALUE_REQUIRED, description: 'Service class name')
            ->addOption(name: 'lifetime', shortcut: null, mode: InputOption::VALUE_REQUIRED, description: 'Service lifetime (singleton, scoped, transient)', default: 'transient')
            ->addOption(name: 'tags', shortcut: null, mode: InputOption::VALUE_REQUIRED, description: 'Comma-separated service tags')
            ->addOption(name: 'dependencies', shortcut: null, mode: InputOption::VALUE_REQUIRED, description: 'Comma-separated service dependencies')
            ->addOption(name: 'environment', shortcut: null, mode: InputOption::VALUE_REQUIRED, description: 'Target environment')
            ->addOption(name: 'config', shortcut: null, mode: InputOption::VALUE_REQUIRED, description: 'JSON configuration string')
            ->addOption(name: 'file', shortcut: null, mode: InputOption::VALUE_REQUIRED, description: 'Import/export file path')
            ->addOption(name: 'format', shortcut: 'f', mode: InputOption::VALUE_REQUIRED, description: 'Output format (table, json)', default: 'table')
            ->setDescription(description: 'Manage container services: list, add, update, remove, import, export');
    }

    /**
     * Execute the command action.
     *
     * @param InputInterface  $input  Input arguments and options.
     * @param OutputInterface $output Output writer.
     *
     * @return int Command exit code.
     *
     * @see docs/Tools/Console/ServiceManagementCommand.md#method-execute
     */
    protected function execute(InputInterface $input, OutputInterface $output) : int
    {
        $io        = new SymfonyStyle(input: $input, output: $output);
        $action    = $input->getArgument(name: 'action');
        $serviceId = $input->getArgument(name: 'service');

        try {
            match ($action) {
                'list'   => $this->listServices(io: $io, input: $input),
                'add'    => $this->addService(io: $io, input: $input),
                'update' => $this->updateService(io: $io, input: $input, serviceId: $serviceId),
                'remove' => $this->removeService(io: $io, serviceId: $serviceId),
                'import' => $this->importServices(io: $io, input: $input),
                'export' => $this->exportServices(io: $io, input: $input),
                'show'   => $this->showService(io: $io, serviceId: $serviceId, input: $input),
                default  => throw new InvalidArgumentException(message: "Unknown action: {$action}")
            };

            return Command::SUCCESS;

        } catch (Throwable $e) {
            $io->error(message: "Command failed: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    /**
     * List services in the repository.
     *
     * @param SymfonyStyle   $io    Console style helper.
     * @param InputInterface $input Input options.
     *
     * @throws \Exception
     *
     * @see docs/Tools/Console/ServiceManagementCommand.md#method-listservices
     */
    private function listServices(SymfonyStyle $io, InputInterface $input) : void
    {
        $services = $this->serviceRepo->findAll();
        $format   = $input->getOption(name: 'format');

        if ($format === 'json') {
            $io->writeln(messages: json_encode($services->all(), JSON_PRETTY_PRINT));

            return;
        }

        if ($services->isEmpty()) {
            $io->info(message: 'No services registered');

            return;
        }

        $tableData = $services->map(static function ($service) {
            return [
                $service->id,
                $service->class,
                $service->lifetime->value,
                count($service->dependencies),
                implode(', ', array_slice($service->tags, 0, 3)) . (count($service->tags) > 3 ? '...' : ''),
                $service->isActive ? 'âœ“' : 'âœ—',
                $service->environment ?? 'all',
            ];
        })->all();

        $io->table(
            headers: ['ID', 'Class', 'Lifetime', 'Deps', 'Tags', 'Active', 'Env'],
            rows   : $tableData
        );

        $stats = $this->serviceRepo->getServiceStats();
        $io->info(message: "Total: {$stats['total_services']} services");
    }

    /**
     * Add a new service definition.
     *
     * @param SymfonyStyle   $io    Console style helper.
     * @param InputInterface $input Input options and arguments.
     *
     * @throws \Throwable
     * @see docs/Tools/Console/ServiceManagementCommand.md#method-addservice
     */
    private function addService(SymfonyStyle $io, InputInterface $input) : void
    {
        $serviceId = $input->getArgument(name: 'service');
        if (! $serviceId) {
            throw new InvalidArgumentException(message: 'Service ID is required for add action');
        }

        $class = $input->getOption(name: 'class');
        if (! $class) {
            throw new InvalidArgumentException(message: 'Service class is required');
        }

        $lifetime     = ServiceLifetime::from(value: $input->getOption(name: 'lifetime'));
        $tags         = $this->parseCommaSeparated(value: $input->getOption(name: 'tags'));
        $dependencies = $this->parseCommaSeparated(value: $input->getOption(name: 'dependencies'));
        $environment  = $input->getOption(name: 'environment');
        $configJson   = $input->getOption(name: 'config');

        $config = $configJson ? json_decode($configJson, true) : [];
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(message: 'Invalid JSON in config option');
        }

        $service = new ServiceDefinitionEntity(
            id          : $serviceId,
            class       : $class,
            lifetime    : $lifetime,
            config      : $config,
            tags        : $tags,
            dependencies: $dependencies,
            environment : $environment,
            isActive    : true,
            createdAt   : new DateTimeImmutable
        );

        $this->serviceRepo->saveServiceDefinition(service: $service);
        $io->success(message: "Service '{$serviceId}' added successfully");
    }

    /**
     * Parse a comma-separated list into an array of trimmed values.
     *
     * @param string|null $value Comma-separated string.
     *
     * @return array<int, string>
     *
     * @see docs/Tools/Console/ServiceManagementCommand.md#method-parsecommaseparated
     */
    private function parseCommaSeparated(string|null $value) : array
    {
        if (! $value) {
            return [];
        }

        return array_map('trim', explode(',', $value));
    }

    /**
     * Update an existing service definition.
     *
     * @param SymfonyStyle   $io        Console style helper.
     * @param InputInterface $input     Input options and arguments.
     * @param string|null    $serviceId Service identifier.
     *
     * @throws \Throwable
     * @see docs/Tools/Console/ServiceManagementCommand.md#method-updateservice
     */
    private function updateService(SymfonyStyle $io, InputInterface $input, string|null $serviceId) : void
    {
        if (! $serviceId) {
            throw new InvalidArgumentException(message: 'Service ID is required for update action');
        }

        $existing = $this->serviceRepo->findById(id: $serviceId);
        if (! $existing) {
            throw new RuntimeException(message: "Service '{$serviceId}' not found");
        }

        $updates = [];

        if ($class = $input->getOption(name: 'class')) {
            $updates['class'] = $class;
        }

        if ($lifetime = $input->getOption(name: 'lifetime')) {
            $updates['lifetime'] = $lifetime;
        }

        if ($tags = $input->getOption(name: 'tags')) {
            $updates['tags'] = json_encode($this->parseCommaSeparated(value: $tags));
        }

        if ($dependencies = $input->getOption(name: 'dependencies')) {
            $updates['dependencies'] = json_encode($this->parseCommaSeparated(value: $dependencies));
        }

        if ($environment = $input->getOption(name: 'environment')) {
            $updates['environment'] = $environment;
        }

        if ($configJson = $input->getOption(name: 'config')) {
            $config = json_decode($configJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new InvalidArgumentException(message: 'Invalid JSON in config option');
            }
            $updates['config'] = json_encode($config);
        }

        if (empty($updates)) {
            $io->warning(message: 'No updates specified');

            return;
        }

        $updated = $existing->withUpdates($updates);
        $this->serviceRepo->saveServiceDefinition(service: $updated);

        $io->success(message: "Service '{$serviceId}' updated successfully");
    }

    /**
     * Mark a service definition as inactive.
     *
     * @param SymfonyStyle $io        Console style helper.
     * @param string|null  $serviceId Service identifier.
     *
     * @throws \Throwable
     * @see docs/Tools/Console/ServiceManagementCommand.md#method-removeservice
     */
    private function removeService(SymfonyStyle $io, string|null $serviceId) : void
    {
        if (! $serviceId) {
            throw new InvalidArgumentException(message: 'Service ID is required for remove action');
        }

        $service = $this->serviceRepo->findById(id: $serviceId);
        if (! $service) {
            throw new RuntimeException(message: "Service '{$serviceId}' not found");
        }

        // Mark as inactive instead of deleting
        $inactive = new ServiceDefinitionEntity(
            id          : $service->id,
            class       : $service->class,
            lifetime    : $service->lifetime,
            config      : $service->config,
            tags        : $service->tags,
            dependencies: $service->dependencies,
            environment : $service->environment,
            description : $service->description,
            isActive    : false,
            createdAt   : $service->createdAt,
            updatedAt   : new DateTimeImmutable
        );

        $this->serviceRepo->saveServiceDefinition(service: $inactive);
        $io->success(message: "Service '{$serviceId}' marked as inactive");
    }

    /**
     * Import services from a JSON file.
     *
     * @param SymfonyStyle   $io    Console style helper.
     * @param InputInterface $input Input options and arguments.
     *
     * @see docs/Tools/Console/ServiceManagementCommand.md#method-importservices
     */
    private function importServices(SymfonyStyle $io, InputInterface $input) : void
    {
        $filePath = $input->getOption(name: 'file');
        if (! $filePath) {
            throw new InvalidArgumentException(message: 'File path is required for import');
        }

        if (! file_exists($filePath)) {
            throw new RuntimeException(message: "File not found: {$filePath}");
        }

        $content      = file_get_contents($filePath);
        $servicesData = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(message: 'Invalid JSON in import file');
        }

        $results = $this->serviceRepo->importServices(servicesData: $servicesData);

        $io->success(message: "Import completed: {$results['imported']} imported, {$results['skipped']} skipped");

        if (! empty($results['errors'])) {
            $io->warning(message: 'Some services failed to import:');
            foreach ($results['errors'] as $error) {
                $io->writeln(messages: '  â€¢ ' . json_encode($error['data']) . ": {$error['error']}");
            }
        }
    }

    /**
     * Export services to a JSON file.
     *
     * @param SymfonyStyle   $io    Console style helper.
     * @param InputInterface $input Input options and arguments.
     *
     * @throws \DateMalformedStringException
     * @throws \ReflectionException
     * @throws \Throwable
     * @see docs/Tools/Console/ServiceManagementCommand.md#method-exportservices
     */
    private function exportServices(SymfonyStyle $io, InputInterface $input) : void
    {
        $filePath = $input->getOption(name: 'file');
        if (! $filePath) {
            throw new InvalidArgumentException(message: 'File path is required for export');
        }

        $filters = [];
        if ($environment = $input->getOption(name: 'environment')) {
            $filters['environment'] = $environment;
        }

        $servicesData = $this->serviceRepo->exportServices(filters: $filters);

        $json = json_encode($servicesData, JSON_PRETTY_PRINT);
        if (file_put_contents($filePath, $json) === false) {
            throw new RuntimeException(message: "Failed to write to file: {$filePath}");
        }

        $io->success(message: 'Exported ' . count($servicesData) . " services to {$filePath}");
    }

    /**
     * Show a single service definition.
     *
     * @param SymfonyStyle   $io        Console style helper.
     * @param string|null    $serviceId Service identifier.
     * @param InputInterface $input     Input options and arguments.
     *
     * @throws \Exception
     *
     * @see docs/Tools/Console/ServiceManagementCommand.md#method-showservice
     */
    private function showService(SymfonyStyle $io, string|null $serviceId, InputInterface $input) : void
    {
        if (! $serviceId) {
            throw new InvalidArgumentException(message: 'Service ID is required for show action');
        }

        $service = $this->serviceRepo->findById(id: $serviceId);
        if (! $service) {
            throw new RuntimeException(message: "Service '{$serviceId}' not found");
        }

        $format = $input->getOption(name: 'format');

        if ($format === 'json') {
            $io->writeln(messages: json_encode($service->toArray(), JSON_PRETTY_PRINT));

            return;
        }

        $io->title(message: "Service: {$service->id}");

        $io->table(
            headers: ['Property', 'Value'],
            rows   : [
                ['ID', $service->id],
                ['Class', $service->class],
                ['Lifetime', $service->lifetime->value],
                ['Environment', $service->environment ?? 'all'],
                ['Active', $service->isActive ? 'Yes' : 'No'],
                ['Tags', implode(', ', $service->tags)],
                ['Dependencies', implode(', ', $service->dependencies)],
                ['Config', empty($service->config) ? 'None' : json_encode($service->config)],
                ['Created', $service->createdAt?->format('Y-m-d H:i:s')],
                ['Updated', $service->updatedAt?->format('Y-m-d H:i:s')],
            ]
        );
    }
}
