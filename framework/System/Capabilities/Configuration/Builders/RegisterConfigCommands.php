<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Configuration\Builders;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Framework\System\Configuration\Foundation\ApplicationConfiguration;
use Avax\Framework\System\Configuration\Foundation\RuntimeConfiguration;
use Avax\Framework\System\Configuration\LoadApplicationConfiguration;
use Avax\Framework\System\Configuration\LoadRuntimeConfiguration;
use Avax\Framework\System\Configuration\ValidateApplicationConfiguration;
use Avax\Framework\System\Configuration\ValidateRuntimeConfiguration;
use Closure;
use Throwable;

/**
 * RegisterConfigCommands — provides CLI command closures for config:inspect, config:validate, config:publish.
 */
final readonly class RegisterConfigCommands
{
    public function __construct(
        private Filesystem $filesystem,
    ) {}

    /**
     * @return array<string, Closure>
     */
    public function __invoke(): array
    {
        return [
            'config:inspect' => $this->configInspectCommand(),
            'config:validate' => $this->configValidateCommand(),
            'config:publish' => $this->configPublishCommand(),
        ];
    }

    private function configInspectCommand(): Closure
    {
        return static function (array $args): string {
            $output = "\033[33mConfiguration Inspect\033[0m\n\n";

            try {
                $appConfig = (new LoadApplicationConfiguration())->load();
                $output .= "Application Configuration:\n";
                $output .= self::formatConfig($appConfig);
                $output .= "\n";
            } catch (Throwable $e) {
                $output .= "\033[31mApplication config load failed: ".$e->getMessage()."\033[0m\n\n";
            }

            try {
                $runtimeConfig = (new LoadRuntimeConfiguration())->load();
                $output .= "Runtime Configuration:\n";
                $output .= self::formatRuntimeConfig($runtimeConfig);
                $output .= "\n";
            } catch (Throwable $e) {
                $output .= "\033[31mRuntime config load failed: ".$e->getMessage()."\033[0m\n\n";
            }

            return $output;
        };
    }

    private function configValidateCommand(): Closure
    {
        return static function (array $args): string {
            $output = "\033[33mConfiguration Validate\033[0m\n\n";
            $failed = false;

            try {
                $appConfig = (new LoadApplicationConfiguration())->load();
                (new ValidateApplicationConfiguration())->validate($appConfig);
                $output .= "\033[32m✓\033[0m Application configuration is valid\n";
            } catch (Throwable $e) {
                $output .= "\033[31m✗\033[0m Application configuration invalid: ".$e->getMessage()."\n";
                $failed = true;
            }

            try {
                $runtimeConfig = (new LoadRuntimeConfiguration())->load();
                (new ValidateRuntimeConfiguration())->validate($runtimeConfig);
                $output .= "\033[32m✓\033[0m Runtime configuration is valid\n";
            } catch (Throwable $e) {
                $output .= "\033[31m✗\033[0m Runtime configuration invalid: ".$e->getMessage()."\n";
                $failed = true;
            }

            $output .= "\n";

            if ($failed) {
                $output .= "\033[31mConfiguration validation failed.\033[0m\n";
            } else {
                $output .= "\033[32mConfiguration validation passed.\033[0m\n";
            }

            return $output;
        };
    }

    private function configPublishCommand(): Closure
    {
        $filesystem = $this->filesystem;

        return static function (array $args) use ($filesystem) : string {
            $output = "\033[33mConfiguration Publish\033[0m\n\n";

            $projectRoot = dirname(__DIR__, 4);
            $configDir = $projectRoot.'/config';

            if (! $filesystem->exists($configDir)) {
                $filesystem->createDirectory($configDir, 0o755);
                $output .= "Created config/ directory\n";
            }

            $appConfigPath = $configDir.'/app.php';
            $runtimeConfigPath = $configDir.'/runtime.php';

            if (! $filesystem->isReadable($appConfigPath)) {
                $defaultConfig = self::defaultAppConfig();
                $filesystem->write($appConfigPath, $defaultConfig);
                $output .= "Published config/app.php\n";
            } else {
                $output .= "config/app.php already exists, skipping\n";
            }

            if (! $filesystem->isReadable($runtimeConfigPath)) {
                $defaultConfig = self::defaultRuntimeConfig();
                $filesystem->write($runtimeConfigPath, $defaultConfig);
                $output .= "Published config/runtime.php\n";
            } else {
                $output .= "config/runtime.php already exists, skipping\n";
            }

            $output .= "\n\033[32mConfiguration publish complete.\033[0m\n";

            return $output;
        };
    }

    private static function formatConfig(ApplicationConfiguration $config): string
    {
        return sprintf(
            "  name:        %s\n  environment: %s\n  debug:       %s\n  timezone:    %s\n  version:     %s\n",
            $config->name,
            $config->environment,
            $config->debug ? 'true' : 'false',
            $config->timezone,
            $config->version,
        );
    }

    private static function formatRuntimeConfig(RuntimeConfiguration $config): string
    {
        return sprintf(
            "  runtime:              %s\n  host:                 %s\n  port:                 %d\n  memory guard soft:    %d MB\n  memory guard hard:    %d MB\n  max requests:         %d\n  warm safety:          %s\n  serve mode:           %s\n",
            $config->defaultRuntime,
            $config->host,
            $config->port,
            $config->memoryGuardSoftLimit / 1024 / 1024,
            $config->memoryGuardHardLimit / 1024 / 1024,
            $config->maxRequests,
            $config->warmSafetyEnabled ? 'enabled' : 'disabled',
            $config->serveMode,
        );
    }

    private static function defaultAppConfig(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

return [
    'name' => 'My AvaX App',
    'environment' => 'development',
    'debug' => true,
    'timezone' => 'UTC',
    'version' => '1.0.0',
];
PHP;
    }

    private static function defaultRuntimeConfig(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

return [
    'default_runtime' => 'built-in',
    'host' => '127.0.0.1',
    'port' => 8000,
    'memory_guard_soft_limit' => 134217728,  // 128 MB
    'memory_guard_hard_limit' => 268435456,  // 256 MB
    'max_requests' => 1000,
    'warm_safety_enabled' => true,
    'serve_mode' => 'http',
];
PHP;
    }
}
