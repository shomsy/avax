<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Routing\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Route
{
    /**
     * @param list<string>          $methods
     * @param list<string|callable> $middleware
     * @param array<string, mixed>  $defaults
     * @param array<string, string> $constraints
     * @param array<string, mixed>  $attributes
     */
    public function __construct(
        public string      $path,
        public string|null $name = null,
        public array|null  $methods = null,
        public array|null  $middleware = null,
        public string|null $domain = null,
        public array|null  $defaults = null,
        public array|null  $constraints = null,
        public array|null  $attributes = null,
        public string|null $authorize = null,
    )
    {
        $this->methods     ??= ['GET'];
        $this->middleware  ??= [];
        $this->defaults    ??= [];
        $this->constraints ??= [];
        $this->attributes  ??= [];
    }
}
