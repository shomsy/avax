<?php

declare(strict_types=1);

namespace Avax\Components\Data\System\Capabilities\DataTransfer\Capabilities\Attributes;

use Attribute;

#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class Required {}
