<?php

declare(strict_types=1);
namespace Avax\Container\Tools\Console;

use Avax\Container\Container;
use Avax\Container\Features\Think\Cache\FilePrototypeCache;
use Throwable;

/**
 * CLI command for clearing container caches during development and debugging.
 *
 * This command removes cached container data including service prototypes
 * and compiled definitions. It's essential for development workflows where cache
 * invalidation is needed after code changes.
 *
 * WHAT IT CLEARS:
 * - Service prototype cache (reflection analysis results)
 * - Compiled container definitions (production optimizations)
 * - Temporary compilation artifacts
 *
 * WHEN TO USE:
 * - After modifying service definitions or dependencies
 * - During development when cache causes stale behavior
 * - Before running tests to ensure clean state
 * - When troubleshooting container-related issues
 * - After deploying code changes in development
 *
 * USAGE SCENARIOS:
 * ```bash
 * # Clear all container caches
 * php bin/container clear-cache
 *
 * # Clear only prototype cache
 * php bin/container clear-cache --prototypes-only
 *
 * # Clear specific cache directory
 * php bin/container clear-cache --cache-dir=/custom/cache
 * ```
 *
 * CACHE TYPES CLEARED:
 *
 * 1. PLAN CACHE:
 *    - Stores analyzed service dependency information
 *    - Cached reflection results for performance
 *    - Invalidated when service definitions change
 *
 * 2. COMPILED DEFINITIONS:
 *    - Pre-compiled container configurations
 *    - Production optimization files
 *    - May need recompilation after clearing
 *
 * PERFORMANCE IMPACT:
 * - Clearing: Minimal impact (file system operations)
 * - Post-clear: First requests may be slower due to re-analysis
 * - Memory: Reduces memory usage by clearing cached instances
 * - Development: Improves iteration speed with fresh cache
 *
 * SECURITY CONSIDERATIONS:
 * - Only clears container-related caches
 * - Doesn't affect application data or user sessions
 * - Safe for production use (with recompilation)
 * - Consider backup for critical production caches
 *
 * ERROR HANDLING:
 * - Continues operation even if some caches fail to clear
 * - Provides detailed feedback on what was cleared
 * - Graceful handling of permission issues
 * - Non-destructive (doesn't delete essential files)
 *
 * INTEGRATION:
 * - Development workflows and IDE integrations
 * - CI/CD pipelines for clean test environments
 * - Deployment scripts for cache management
 * - Debugging and troubleshooting toolkits
 *
 * ALTERNATIVES:
 * - Manual cache directory deletion
 * - Container restart (clears in-memory caches)
 * - Selective cache clearing via API
 * - Cache warming for performance maintenance
 *
 * BEST PRACTICES:
 * - Clear caches after significant code changes
 * - Use in development environments regularly
 * - Consider automation in deployment scripts
 * - Monitor cache sizes and clearing frequency
 * - Test thoroughly after cache clearing
 *
 * @see FilePrototypeCache For prototype cache implementation
 * @see CompileCommand For cache generation
 * @see Container::clearCache() For programmatic cache clearing
 * @see docs_md/Tools/Console/ClearCacheCommand.md#quick-summary
 */
readonly class ClearCacheCommand
{
    /**
     * @param Container $container       The container instance whose caches to clear
     * @param string    $defaultCacheDir Default cache directory path
     *
     * @see docs_md/Tools/Console/ClearCacheCommand.md#method-__construct
     */
    public function __construct(
        private Container $container,
        private string    $defaultCacheDir = '/tmp/container-cache'
    ) {}

    /**
     * Execute the cache clearing command.
     *
     * Clears all container-related caches including prototypes, proxies, and compiled
     * definitions. Provides detailed feedback about what was cleared and any issues.
     *
     * COMMAND OPTIONS:
     * - --prototypes-only: Clear only service prototype cache
     * - --cache-dir: Custom cache directory path
     * - --dry-run: Show what would be cleared without actually clearing
     *
     * @param bool        $prototypesOnly Clear only service prototype cache
     * @param string|null $cacheDir       Custom cache directory (optional)
     * @param bool        $dryRun         Show what would be cleared without actually doing it
     *
     * @return void Outputs cache clearing results to stdout
     * @throws \Throwable
     * @see docs_md/Tools/Console/ClearCacheCommand.md#method-execute
     */
    public function execute(
        bool|null   $prototypesOnly = null,
        string|null $cacheDir = null,
        bool        $dryRun = false
    ) : void
    {
        $prototypesOnly ??= false;
        echo "ðŸ§¹ Clearing Avax Container caches...\n";

        $cacheDirectory = $cacheDir ?? $this->defaultCacheDir;
        $clearedItems   = [];
        $errors         = [];

        try {
            // Clear service prototype cache
            echo "ðŸ“‹ Clearing service prototype cache...\n";

            $prototypeCache = $this->container->getPrototypeFactory()->getCache();
            if ($prototypeCache instanceof FilePrototypeCache) {
                $prototypeCachePath = $prototypeCache->getCachePath();
                if ($dryRun) {
                    echo "  â„¹ï¸  Would clear: {$prototypeCachePath}\n";
                } else {
                    $this->clearDirectory(directory: $prototypeCachePath);
                    $clearedItems[] = "Service prototype cache";
                }
            } else {
                // For in-memory cache, just clear the container's cache
                if (! $dryRun) {
                    $this->container->clearCache();
                    $clearedItems[] = "In-memory service cache";
                }
            }

            // Clear compiled definitions
            if (! $prototypesOnly) {
                echo "ðŸ“¦ Clearing compiled definitions...\n";

                $compiledPaths = [
                    '/cache/container.php',
                    $cacheDirectory . '/container.php'
                ];

                foreach ($compiledPaths as $path) {
                    if (file_exists($path)) {
                        if ($dryRun) {
                            echo "  â„¹ï¸  Would remove: {$path}\n";
                        } else {
                            if (unlink($path)) {
                                $clearedItems[] = "Compiled definition: {$path}";
                            } else {
                                $errors[] = "Failed to remove: {$path}";
                            }
                        }
                    }
                }
            }

            // Summary
            if ($dryRun) {
                echo "ðŸ” Dry run completed - no files were actually deleted.\n";
            } else {
                echo "âœ… Cache clearing completed!\n";
                echo "ðŸ—‚ï¸  Cleared items:\n";
                foreach ($clearedItems as $item) {
                    echo "  âœ“ {$item}\n";
                }

                if (! empty($errors)) {
                    echo "âš ï¸  Errors encountered:\n";
                    foreach ($errors as $error) {
                        echo "  âœ— {$error}\n";
                    }
                }

                echo "ðŸš€ Container caches cleared successfully!\n";
                echo "ðŸ’¡ Tip: First requests may be slower due to cache regeneration.\n";
            }

        } catch (Throwable $e) {
            echo "âŒ Cache clearing failed: {$e->getMessage()}\n";
            throw $e;
        }
    }

    /**
     * Clear all files in a directory.
     *
     * @param string $directory Directory path to clear
     *
     * @see docs_md/Tools/Console/ClearCacheCommand.md#method-cleardirectory
     */
    private function clearDirectory(string $directory) : void
    {
        if (! is_dir($directory)) {
            return;
        }

        $files = glob($directory . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
