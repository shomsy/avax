<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\BootApplication;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Framework\System\Foundation\Time\Clock;
use Avax\Framework\System\PublicSurface\App;
use Avax\Framework\System\PublicSurface\BootDsl;

/**
 * BootWithDsl — flow: execute the full Boot DSL lifecycle.
 *
 * This flow is the composition root for Boot DSL boot.
 * It receives declarative inputs and returns a ready App.
 */
final readonly class BootWithDsl
{
    /**
     * @param list<class-string<ServiceProvider>> $providers
     */
    public function boot(
        string $projectPath,
        string $environmentName = 'production',
        array $providers = [],
        ?Clock $clock = null,
        string $runtimeName = 'avax',
    ): App {
        $builder = BootDsl::make()
            ->from(projectPath: $projectPath, environmentName: $environmentName)
            ->withProviders($providers)
            ->withRuntimeName($runtimeName);

        if ($clock !== null) {
            $builder = $builder->withClock($clock);
        }

        return $builder->create();
    }
}
