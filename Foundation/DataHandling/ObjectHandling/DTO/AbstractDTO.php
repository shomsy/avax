<?php

declare(strict_types=1);

namespace Avax\DataHandling\ObjectHandling\DTO;

use Avax\DataHandling\ObjectHandling\DTO\Traits\CastsTypes;
use Avax\DataHandling\ObjectHandling\DTO\Traits\HandlesAttributes;
use Avax\DataHandling\ObjectHandling\DTO\Traits\HandlesHydration;
use Avax\DataHandling\ObjectHandling\DTO\Traits\InspectsProperties;
use Avax\DataHandling\ObjectHandling\DTO\Traits\Serialization;

/**
 * Base abstract class for Data Transfer Objects (DTOs).
 *
 * This class provides the foundational structure for Data Transfer Objects within the application.
 * It is responsible for implementing shared behavior and logic such as:
 *
 * - Hydration from associative arrays.
 * - Property casting based on defined types.
 * - Attribute handling and lifecycle interactions.
 * - Inspection of available properties.
 * - Efficient serialization for DTO representations.
 *
 * The use of traits ensures a modular and reusable design, promoting separation of concerns.
 */
abstract class AbstractDTO
{
    /**
     * Include the `HandlesHydration` trait.
     *
     * Provides functionality for mapping external data (like arrays) into object properties.
     */
    use HandlesHydration;

    /**
     * Include the `CastsTypes` trait.
     *
     * Enables strict casting of properties into specified types, ensuring type safety when working
     * with data.
     */
    use CastsTypes;

    /**
     * Include the `HandlesAttributes` trait.
     *
     * Adds methods to manipulate and interact with internal object attributes dynamically.
     */
    use HandlesAttributes;

    /**
     * Include the `InspectsProperties` trait.
     *
     * Adds utilities to inspect the state of object properties during runtime, allowing access to
     * their metadata or dynamic availability checks.
     */
    use InspectsProperties;

    /**
     * Include the `Serialization` trait.
     *
     * Defines methods for serializing and deserializing object data to formats like arrays or JSON,
     * ensuring compatibility with external systems.
     */
    use Serialization;

    /**
     * Constructor with array hydration capability.
     *
     * Constructs a new instance of the Data Transfer Object (DTO) from an array of data.
     * This constructor leverages the `HandlesHydration` trait to populate DTO properties
     * with data provided in the array.
     *
     * @param array $data The associative array containing the initial properties of the DTO.
     *
     * @throws \ReflectionException If reflection fails to evaluate class or property metadata during hydration.
     */
    public function __construct(array $data)
    {
        // Hydrate the object with the given data array.
        $this->hydrateFrom($data);
    }
}