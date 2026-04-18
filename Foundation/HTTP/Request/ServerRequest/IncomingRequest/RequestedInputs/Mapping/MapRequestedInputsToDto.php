<?php

declare(strict_types=1);

namespace Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\Mapping;

use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\HTTP\Request\ServerRequest\IncomingRequest\RequestedInputs\RequestedInputs;
use InvalidArgumentException;
use ReflectionException;
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
        if (! class_exists($dtoClass)) {
            throw new InvalidArgumentException(
                message: sprintf('DTO class does not exist: %s', $dtoClass)
            );
        }

        if (! is_a($dtoClass, AbstractDTO::class, allow_string: true)) {
            throw new InvalidArgumentException(
                message: sprintf('DTO class "%s" must extend %s', $dtoClass, AbstractDTO::class)
            );
        }

        try {
            /** @var T $dto */
            $dto = new $dtoClass($inputs->all());
        } catch (ReflectionException $e) {
            throw new RuntimeException(
                message : sprintf('Failed to instantiate DTO "%s": %s', $dtoClass, $e->getMessage()),
                previous: $e,
            );
        }

        return $dto;
    }
}
