<?php

declare(strict_types=1);

/**
 * Defines an attribute to validate that a property is an image.
 *
 * This attribute should be used to annotate properties that are expected to
 * hold image file names or paths. The validation logic checks the file
 * extension against a pre-defined list of allowed image formats.
 *
 * Usage:
 * #[Image]
 * private $imageProperty;
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Validate that the provided file's extension is among the allowed image types.
 *
 * INTENT: Ensure that the input value is a valid image format to prevent
 * invalid data from entering the system, which could cause errors downstream.
 * This method throws an exception if validation fails to enforce strict conformity.
 *
 * @throws \Avax\Exceptions\ValidationException If the file extension is not permitted.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class Image
{
    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        $allowedMimes = ['jpeg', 'png', 'gif', 'bmp', 'svg', 'webp'];
        $extension    = strtolower(pathinfo((string) $value, PATHINFO_EXTENSION));
        if (! in_array($extension, $allowedMimes, true)) {
            throw new ValidationException(message: $property . ' must be an image.');
        }
    }
}
