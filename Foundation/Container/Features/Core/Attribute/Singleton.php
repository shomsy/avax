<?php

declare(strict_types=1);
namespace Avax\Container\Features\Core\Attribute;

use Attribute;

#[Attribute(flags: Attribute::TARGET_CLASS)]
final class Singleton {}