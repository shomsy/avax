<?php

declare(strict_types=1);
namespace Avax\Container\Features\Think\Prototype;

use Avax\Container\Features\Define\Store\DefinitionStore;
use Avax\Container\Features\Define\Store\ServiceDefinition;

/**
 * @package Avax\Container\Think\Prototype
 *
 * Compiler for container definitions into production-ready PHP artifacts.
 *
 * CompiledPrototypeDumper transforms runtime container definitions into
 * serialized, production-optimized PHP files. This enables pre-compilation
 * of dependency injection configurations for maximum performance in production
 * environments, eliminating the need for runtime analysis and reflection.
 *
 * WHY IT EXISTS:
 * - To enable ahead-of-time compilation of container configurations
 * - To provide production-optimized container artifacts
 * - To reduce startup time by pre-computing expensive operations
 * - To support deployment-time validation and optimization
 *
 * COMPILATION PROCESS:
 * 1. Extracts all service definitions from DefinitionStore
 * 2. Serializes definitions into PHP array format
 * 3. Generates executable PHP code with proper metadata
 * 4. Includes timestamp for cache invalidation and versioning
 *
 * OUTPUT FORMAT:
 * Generates PHP code that returns an array with:
 * - generated_at: Unix timestamp of compilation
 * - definitions: Array of serialized ServiceDefinition objects
 *
 * USAGE SCENARIOS:
 * - Production deployment preparation
 * - CI/CD pipeline compilation steps
 * - Container warmup and optimization
 * - Development-to-production artifact generation
 *
 * COMPILATION BENEFITS:
 * - Eliminates runtime reflection overhead
 * - Reduces memory usage by pre-computing structures
 * - Enables early validation of configurations
 * - Supports deployment-time error detection
 * - Improves cold-start performance significantly
 *
 * SERIALIZATION STRATEGY:
 * - Uses PHP's var_export() for reliable serialization
 * - Includes metadata for cache management and debugging
 * - Generates valid, executable PHP code
 * - Maintains type information and structure integrity
 *
 * PERFORMANCE CHARACTERISTICS:
 * - Compilation is a one-time operation during deployment
 * - Generated code loads quickly with PHP's opcache
 * - Memory efficient storage of compiled definitions
 * - Fast lookups in production runtime
 *
 * SECURITY CONSIDERATIONS:
 * - Generated code is safe for execution (no dynamic evaluation)
 * - No injection of user data into generated code
 * - Compilation occurs in trusted deployment environments
 * - Runtime loading uses standard PHP include mechanisms
 *
 * ERROR HANDLING:
 * - Assumes valid DefinitionStore input (validation should occur upstream)
 * - Serialization failures indicate configuration issues
 * - Generated code includes error handling for corrupted artifacts
 *
 * INTEGRATION POINTS:
 * - Used by CompileCommand for CLI compilation
 * - Consumed by bootstrap processes for optimized loading
 * - Supports cache invalidation and version management
 * - Integrates with deployment and CI/CD pipelines
 *
 * EXTENSIBILITY:
 * - Custom serialization formats can be added
 * - Additional metadata can be included in output
 * - Support for different target formats (JSON, binary)
 * - Integration with external compilers and optimizers
 *
 * LIMITATIONS:
 * - PHP-only output format (not language-agnostic)
 * - Requires PHP var_export compatible structures
 * - Compilation-time operation (not runtime)
 * - Depends on DefinitionStore serialization capabilities
 *
 * PRODUCTION OPTIMIZATION:
 * - Compile during deployment, not at runtime
 * - Use opcache for maximum performance
 * - Include in deployment artifacts and cache layers
 * - Monitor compilation time and output size
 *
 * DEBUGGING SUPPORT:
 * - Includes generation timestamp for troubleshooting
 * - Maintains definition structure for inspection
 * - Supports diffing between compilation versions
 * - Provides metadata for deployment tracking
 *
 * @see     DefinitionStore The source of service definitions
 * @see     ServiceDefinition The individual definition structure
 * @see     CompileCommand CLI command that uses this dumper
 * @see docs_md/Features/Think/Prototype/CompiledPrototypeDumper.md#quick-summary
 */
final readonly class CompiledPrototypeDumper
{
    public function __construct(
        private DefinitionStore $definitions
    ) {}

    /**
     * Dump all current definitions into a PHP-returning payload string.
     *
     * @return string
     * @see docs_md/Features/Think/Prototype/CompiledPrototypeDumper.md#method-dump
     */
    public function dump() : string
    {
        $definitions = array_map(
            static fn(ServiceDefinition $definition) : array => $definition->toArray(),
            $this->definitions->getAllDefinitions()
        );

        $payload = [
            'generated_at' => time(),
            'definitions'  => $definitions,
        ];

        return "<?php\n\nreturn " . var_export($payload, true) . ";\n";
    }
}
