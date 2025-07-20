<?php

declare(strict_types=1);

namespace Gemini\DataHandling\Validation\Attributes;

use Attribute;

/**
 * Attribute Hidden
 *
 * Marks a DTO property as hidden from serialization (toArray, toJson).
 *
 * Pure marker – contains no logic. Interpreted by Serialization trait.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class Hidden
{
    //
}
