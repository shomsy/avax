<?php

declare(strict_types=1);

/**
 * Attribute class to validate image dimensions for a property in a DTO.
 *
 * This class checks various constraints like specific width, height,
 * minimum and maximum dimensions, and aspect ratio. It is designed to
 * be used as an attribute on properties within data transfer objects (DTOs).
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;
use Exception;

/**
 * This readonly class is used to encapsulate the dimensions of an image and ensure they adhere to specified validation
 * rules. The readonly modifier ensures immutability, which is crucial for maintaining consistency in image validation
 * parameters.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class ImageDimension
{
    private float|null $ratio;
    private int|null   $max_height;
    private int|null   $max_width;
    private int|null   $min_height;
    private int|null   $min_width;
    private int|null   $height;
    private int|null   $width;

    public function __construct(
        int|null   $width = null,
        int|null   $height = null,
        int|null   $min_width = null,
        int|null   $min_height = null,
        int|null   $max_width = null,
        int|null   $max_height = null,
        float|null $ratio = null,
    )
    {
        $this->width      = $width;
        $this->height     = $height;
        $this->min_width  = $min_width;
        $this->min_height = $min_height;
        $this->max_width  = $max_width;
        $this->max_height = $max_height;
        $this->ratio      = $ratio;
    }

    /**
     * @throws ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        try {
            $dimensions = getimagesize(filename: $value);

            $width  = $dimensions[0];
            $height = $dimensions[1];

            if ($this->width !== null && $width !== $this->width) {
                throw new ValidationException(message: sprintf('%s must be %d pixels wide.', $property, $this->width));
            }

            if ($this->height !== null && $height !== $this->height) {
                throw new ValidationException(message: sprintf('%s must be %d pixels tall.', $property, $this->height));
            }

            if ($this->min_width !== null && $width < $this->min_width) {
                throw new ValidationException(
                    message: sprintf(
                                 '%s must be at least %d pixels wide.',
                                 $property,
                                 $this->min_width,
                             ),
                );
            }

            if ($this->min_height !== null && $height < $this->min_height) {
                throw new ValidationException(
                    message: sprintf(
                                 '%s must be at least %d pixels tall.',
                                 $property,
                                 $this->min_height,
                             ),
                );
            }

            if ($this->max_width !== null && $width > $this->max_width) {
                throw new ValidationException(
                    message: sprintf('%s may not be greater than %d pixels wide.', $property, $this->max_width),
                );
            }

            if ($this->max_height !== null && $height > $this->max_height) {
                throw new ValidationException(
                    message: sprintf('%s may not be greater than %d pixels tall.', $property, $this->max_height),
                );
            }

            if ($this->ratio !== null && abs(num: $width / $height - $this->ratio) > 0.0001) {
                throw new ValidationException(message: sprintf('%s aspect ratio must be %s.', $property, $this->ratio));
            }
        } catch (Exception) {
            throw new ValidationException(message: $property . ' has invalid image dimensions.');
        }
    }
}
