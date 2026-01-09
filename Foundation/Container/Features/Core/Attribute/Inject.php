<?php

declare(strict_types=1);
namespace Avax\Container\Features\Core\Attribute;

use Attribute;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
final class Inject
{
    public function __construct(
        public string|null $abstract = null
    ) {}
}