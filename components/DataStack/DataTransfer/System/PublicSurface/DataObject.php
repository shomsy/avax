<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\DataTransfer\System\PublicSurface;

use stdClass;

/**
 * DataObject — base class for typed transfer objects.
 *
 * Canonical style: public typed properties + PHP attributes + no constructor.
 *
 * Example:
 * ```
 * final class UserInput extends DataObject
 * {
 *     #[Required]
 *     #[Email]
 *     public string $email;
 * }
 * ```
 *
 * DataObject is not HTTP-specific.
 * DataObject does not know about SecureRequest.
 * DataObject does not call Container.
 * DataObject does not perform heavy engine behavior.
 */
abstract class DataObject
{
    /**
     * Convert to array via DataTransfer.
     *
     * @return array<string, mixed>
     */
    public function toArray() : array
    {
        return DataTransfer::toArray(object: $this);
    }

    /**
     * Convert to JSON via DataTransfer.
     */
    public function toJson(int $flags = 0) : string
    {
        return DataTransfer::toJson(object: $this, flags: $flags);
    }

    /**
     * Convert to stdClass via DataTransfer.
     */
    public function toStdClass() : stdClass
    {
        return DataTransfer::toStdClass(object: $this);
    }
}
