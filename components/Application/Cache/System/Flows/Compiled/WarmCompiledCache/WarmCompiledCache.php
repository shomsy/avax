<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Compiled\WarmCompiledCache;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheDirectory;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheManifest;
use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use Avax\Components\Application\Cache\System\Flows\Compiled\CompileCache\CompileCache;
use Closure;
use Throwable;



final class CompiledCacheArtifactDefinition
{
    public function __construct(
        public string               $name,
        public Closure              $builder,
        public CompiledCacheSources $compiledCacheSources,
    ) {}
}