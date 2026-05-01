<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\PublicSurface\Read;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheSources;
use InvalidArgumentException;
use Override;

readonly class CompiledCacheTarget implements CacheReadTarget
{
    public function __construct(
        public string               $name,
        public mixed                $builder,
        public CompiledCacheSources $sources,
    )
    {
        if (! is_callable($this->builder)) {
            throw new InvalidArgumentException(message: 'Compiled cache target builder must be callable');
        }
    }

    public static function artifact(
        string               $name,
        callable             $builder,
        CompiledCacheSources $sources,
    ) : self
    {
        return new self(name: $name, builder: $builder, sources: $sources);
    }

    #[Override]
    public function kind() : CacheReadKind
    {
        return CacheReadKind::COMPILED;
    }

    public function builder() : callable
    {
        return $this->builder;
    }
}
