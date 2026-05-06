<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Compiled\WarmCompiledCache;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use Closure;

final class WarmCompiledCache
{
    public CompiledCacheSources $sources;

    public function __construct(
        public string $name,
        public Closure $builder,
        public CompiledCacheSources $compiledCacheSources,
    ) {
        $this->sources = $compiledCacheSources;
    }
}
