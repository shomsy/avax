<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\System\Flows\RegisterRoutes\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Route
{
    public string|null $authorize   = null;
    public array|null  $attributes  = null;
    public array|null  $constraints = null;
    public array|null  $defaults    = null;
    public string|null $domain      = null;
    public array|null  $middleware  = null;
    public array|null  $methods     = null;
    public string|null $name        = null;
    public string      $path;

    /**
     * @param list<string>          $methods
     * @param list<string|callable> $middleware
     * @param array<string, mixed>  $defaults
     * @param array<string, string> $constraints
     * @param array<string, mixed>  $attributes
     */
    public function __construct(
        string      $path,
        string|null $name = null,
        array|null  $methods = null,
        array|null  $middleware = null,
        string|null $domain = null,
        array|null  $defaults = null,
        array|null  $constraints = null,
        array|null  $attributes = null,
        string|null $authorize = null,
    )
    {
        $this->path        = $path;
        $this->name        = $name;
        $this->methods     = $methods;
        $this->middleware  = $middleware;
        $this->domain      = $domain;
        $this->defaults    = $defaults;
        $this->constraints = $constraints;
        $this->attributes  = $attributes;
        $this->authorize   = $authorize;
        $this->methods     ??= ['GET'];
        $this->middleware  ??= [];
        $this->defaults    ??= [];
        $this->constraints ??= [];
        $this->attributes  ??= [];
    }
}
