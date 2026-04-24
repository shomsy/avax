<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class Hidden {}
