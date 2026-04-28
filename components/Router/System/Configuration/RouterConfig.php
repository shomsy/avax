<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Configuration;

use Avax\Components\Router\System\Capabilities\RouteDefinition\DuplicatePolicy;

/**
 * Configuration object for Router behavior customization.
 */
final readonly class RouterConfig
{
    public int             $maxRoutes;
    public bool            $strictMode;
    public bool            $enableTracing;
    public DuplicatePolicy $duplicatePolicy;

    public function __construct(
        DuplicatePolicy|null $duplicatePolicy = null,
        bool|null            $enableTracing = null,
        bool|null            $strictMode = null,
        int                  $maxRoutes = 10000
    )
    {
        $duplicatePolicy       ??= DuplicatePolicy::THROW;
        $enableTracing         ??= false;
        $strictMode            ??= true;
        $this->duplicatePolicy = $duplicatePolicy;
        $this->enableTracing   = $enableTracing;
        $this->strictMode      = $strictMode;
        $this->maxRoutes       = $maxRoutes;
    }

    public static function development() : self
    {
        return new self(
            duplicatePolicy: DuplicatePolicy::THROW,
            enableTracing  : true,
            strictMode     : false,
            maxRoutes      : 50000
        );
    }

    public static function production() : self
    {
        return new self(
            duplicatePolicy: DuplicatePolicy::THROW,
            enableTracing  : false,
            strictMode     : true,
            maxRoutes      : 10000
        );
    }

    public static function testing() : self
    {
        return new self(
            duplicatePolicy: DuplicatePolicy::REPLACE,
            enableTracing  : true,
            strictMode     : false,
            maxRoutes      : 1000
        );
    }
}
