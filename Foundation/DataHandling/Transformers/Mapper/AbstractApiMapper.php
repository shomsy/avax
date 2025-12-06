<?php

declare(strict_types=1);

namespace Avax\DataHandling\DataTransformers\Mapper;

use Avax\DataHandling\Cache\ReflectionCache;
use Avax\DataHandling\DTO\DTOCollection;
use Avax\DataHandling\DTO\DTOInterface;
use Avax\DataHandling\DTO\Validation\Casting\ValueCaster;
use Avax\DataHandling\DTO\Validation\DTOValidator;
use ReflectionNamedType;
use ReflectionProperty;

/**
 * AbstractApiMapper
 *
 * This abstract class is responsible for mapping API responses to DTO (Data Transfer Object) instances.
 * It utilizes reflection to auto-populate DTO instances, providing flexibility and reusability
 * for different DTO types, including nested DTO structures.
 *
 * ### Example usage:
 * ```
 * class UserApiMapper extends AbstractApiMapper {
 *     protected function defineDtoClass(): string {
 *         return UserDTO::class;
 *     }
 *
 *     protected function defineDtoCollectionClass(): string {
 *         return UserDTOCollection::class;
 *     }
 * }
 *
 * $mapper = new UserApiMapper();
 * $userDto = $mapper->map(data: $data);
 * ```
 *
 * @implements MapperInterface<DTOInterface, DTOCollection>
 */
abstract class AbstractApiMapper implements MapperInterface
{
    /**
     * Maps data to a DTO or a collection of DTOs based on the given data.
     *
     * This method decides whether to map to a single DTO or a collection of DTOs
     * based on the size of the response.
     *
     * @param array $data The array of data sets to be mapped to DTOs.
     *
     * @return DTOInterface|DTOCollection Either a single DTO object or a DTOCollection.
     *
     * @throws \ReflectionException If an error occurs during reflection.
     *
     * ### Example usage:
     * ```
     * $dtoOrCollection = $this->map(data: $apiResponse);
     * ```
     */
    public function map(array $data) : DTOInterface|DTOCollection
    {
        return count(value: $data) === 1
            ? $this->mapSingleDTO(data: $data[0])
            : $this->mapToDTOCollection(data: $data);
    }

    /**
     * Maps a single data array to a DTO object.
     * Internal helper method used by map().
     *
     * @param array $data The data set to be mapped.
     *
     * @return DTOInterface The DTO object created from the provided data.
     *
     * @throws \ReflectionException If an error occurs during reflection.
     *
     * ### Example usage:
     * ```
     * $singleDto = $this->mapSingleDTO(data: $dataArray);
     * ```
     */
    private function mapSingleDTO(array $data) : DTOInterface
    {
        $dtoClass = $this->getDtoClassName();
        DTOValidator::validateClassExistence(dtoClass: $dtoClass);

        return $this->transformToDTO(data: $data, dtoClass: $dtoClass);
    }

    /**
     * Getter method for the DTO class name.
     *
     * @return string The fully qualified class name of the DTO.
     */
    protected function getDtoClassName() : string
    {
        return $this->defineDtoClass();
    }

    /**
     * Abstract method to set the DTO class name for mapping.
     *
     * Child classes must implement this method to provide the DTO class name.
     *
     * @return string The fully qualified class name of the DTO.
     */
    abstract protected function defineDtoClass() : string;

    /**
     * Transforms an array of data into a DTO object.
     *
     * @param array  $data     The data set to be transformed.
     * @param string $dtoClass The fully qualified class name of the DTO.
     *
     * @return DTOInterface The DTO object created from the provided data.
     *
     * @throws \ReflectionException If an error occurs during reflection.
     *
     * ### Example usage:
     * ```
     * $dtoObject = $this->transformToDTO(data: $data, dtoClass: UserDTO::class);
     * ```
     */
    protected function transformToDTO(array $data, string $dtoClass) : DTOInterface
    {
        $castedData = $this->castData(data: $data, dtoClass: $dtoClass);

        return new $dtoClass(...$castedData);
    }

    /**
     * Casts data according to the type defined in the DTO class.
     *
     * This method uses reflection to find the type of each property in the DTO class,
     * and then casts the data accordingly using the `ValueCaster` class.
     *
     * @param array  $data     The data to be cast.
     * @param string $dtoClass The fully qualified class name of the DTO which contains type definitions.
     *
     * @return array An array of casted data that can be used to instantiate the DTO object.
     *
     * @throws \ReflectionException If an error occurs during reflection.
     *
     * ### Example usage:
     * ```
     * $castedData = $this->castData(data: $dataArray, dtoClass: UserDTO::class);
     * ```
     */
    protected function castData(array $data, string $dtoClass) : array
    {
        $reflectionClass = ReflectionCache::getReflectionClass(dtoClass: $dtoClass);
        $castedData      = [];

        foreach ($reflectionClass->getProperties(filter: ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $type         = $reflectionProperty->getType();

            if ($type instanceof ReflectionNamedType && array_key_exists(key: $propertyName, array: $data)) {
                $castedData[$propertyName] = ValueCaster::castValue(
                    value     : $data[$propertyName],
                    type      : $type->getName(),
                    allowsNull: $type->allowsNull(),
                );
            }
        }

        return $castedData;
    }

    /**
     * Maps an array of data sets into an array or collection of DTO objects.
     * Internal helper method used by map().
     *
     * @param array $data The array of data sets to be mapped to DTOs.
     *
     * @return DTOCollection A collection of mapped DTO objects.
     *
     * @throws \ReflectionException If an error occurs during reflection.
     *
     * ### Example usage:
     * ```
     * $dtoCollection = $this->mapToDTOCollection(data: $dataArray);
     * ```
     */
    private function mapToDTOCollection(array $data) : DTOCollection
    {
        $dtoCollection = new DTOCollection();

        foreach ($data as $item) {
            $dtoCollection->add(
                dto: $this->mapSingleDTO(data: $item),
            );
        }

        return $dtoCollection;
    }

    /**
     * Getter method for the DTO collection class name.
     *
     * @return string The fully qualified class name of the DTO collection.
     */
    protected function getDtoCollectionClassName() : string
    {
        return $this->defineDtoCollectionClass();
    }

    /**
     * Abstract method to set the DTO collection class name for mapping.
     *
     * Child classes must implement this method to provide the DTO collection class name.
     *
     * @return string The fully qualified class name of the DTO collection.
     */
    abstract protected function defineDtoCollectionClass() : string;
}
