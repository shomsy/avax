<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Mapping;

use Avax\DataFoundation\ObjectHandling\DTO\AbstractDTO;
use Avax\Components\HTTP\Request\Request;
use Avax\Components\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\RequestedInputs;
use InvalidArgumentException;
use LogicException;
use RuntimeException;

/**
 * MapRequestedInputsToDto
 *
 * DTO mapping adapter for RequestedInputs -> AbstractDTO.
 *
 * This is a thin adapter layer, not a full generic mapper.
 * It delegates hydration and validation to the project's
 * AbstractDTO component.
 */
final readonly class MapRequestedInputsToDto
{
    /**
     * @template T of AbstractDTO
     *
     * @param class-string<T> $dtoClass Must extend AbstractDTO and accept array payload
     *
     * @return T
     *
     * @throws InvalidArgumentException When class does not exist or is not instantiable
     * @throws InvalidArgumentException When class does not extend AbstractDTO
     * @throws RuntimeException When DTO instantiation fails
     */
    public function map(RequestedInputs $inputs, string $dtoClass) : object
    {
        if (! class_exists(class: $dtoClass)) {
            throw new InvalidArgumentException(
                message: sprintf('DTO class does not exist: %s', $dtoClass)
            );
        }

        if (! is_a(object_or_class: $dtoClass, class: AbstractDTO::class, allow_string: true)) {
            throw new InvalidArgumentException(
                message: sprintf('DTO class "%s" must extend %s', $dtoClass, AbstractDTO::class)
            );
        }

        if (is_a(object_or_class: $dtoClass, class: Request::class, allow_string: true)) {
            throw new LogicException(
                message: sprintf(
                             'DTO class "%s" is a Request subclass and cannot be mapped generically from RequestedInputs. Use RequestDtoFactory or controller autowiring instead.',
                             $dtoClass
                         )
            );
        }

        return new $dtoClass($inputs->all());
    }
}
