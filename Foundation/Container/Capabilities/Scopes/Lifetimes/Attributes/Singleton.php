<?php

declare(strict_types=1);

namespace Avax\Container\Capabilities\Scopes\Lifetimes\Attributes;

use Attribute;

/**
 * Singleton Lifecycle Marker Attribute
 *
 * Marks a class to be managed as a singleton service in the container.
 * When applied, only one instance of the class will be created and shared
 * across all resolution requests.
 *
 */
#[Attribute(flags: Attribute::TARGET_CLASS)]
final class Singleton {}
