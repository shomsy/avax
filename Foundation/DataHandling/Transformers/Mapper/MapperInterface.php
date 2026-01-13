<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransformers\Mapper;

use Avax\DataHandling\DTO\DTOCollection;
use Avax\DataHandling\DTO\DTOInterface;

/**
 * MapperInterface
 *
 * This interface defines methods for mapping data to DTO (Data Transfer Object) instances.
 *
 * ### Example Implementation:
 * ```
 * class UserApiMapper extends AbstractApiMapper implements MapperInterface {
 *     protected function defineDtoClass(): string {
 *         return UserDTO::class;
 *     }
 *
 *     protected function defineDtoCollectionClass(): string {
 *         return UserDTOCollection::class;
 *     }
 * }
 * ```
 */
interface MapperInterface
{
    /**
     * Maps data to a DTO or a collection of DTOs based on the given data.
     *
     * This method decides whether to map to a single DTO or a collection of DTOs
     * based on the size of the response.
     *
     * @param  array  $data  The array of data sets to be mapped to DTOs.
     * @return DTOInterface|DTOCollection Either a single DTO object or a DTOCollection.
     *
     * @throws \ReflectionException If an error occurs during reflection.
     *
     * ### Example implementation:
     * ```
     * class UserApiMapper implements MapperInterface {
     *     public function map(array $data): DTOInterface|DTOCollection {
     *         // Implement the mapping logic
     *     }
     * }
     * ```
     */
    public function map(array $data): DTOInterface|DTOCollection;
}
