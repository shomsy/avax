<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Delivery\System\PublicSurface;

use Avax\Components\Operations\Delivery\System\Capabilities\Build\BuildManifest;
use Avax\Components\Operations\Delivery\System\Capabilities\Compile\CompileApplication;
use Avax\Components\Operations\Delivery\System\Capabilities\Manifest\ReleaseManifest;
use Avax\Components\Operations\Delivery\System\Capabilities\Manifest\RollbackPlan;

final readonly class Delivery
{
    public static function build(string $env = 'production'): BuildManifest
    {
        return new BuildManifest($env);
    }

    public static function compile(): CompileApplication
    {
        return new CompileApplication();
    }

    public static function prepare(): ReleaseManifest
    {
        return new ReleaseManifest();
    }

    public static function rollback(ReleaseManifest $releaseManifest): RollbackPlan
    {
        return new RollbackPlan($releaseManifest);
    }
}