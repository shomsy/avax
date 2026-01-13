<?php

declare(strict_types=1);

namespace Avax\DataHandling\ArrayHandling\Traits;

/**
 * This trait provides common query operations for collections.
 * It includes methods for filtering and checking the existence of items based on different conditions.
 */
trait AbstractDependenciesTrait
{
    /**
     * Retrieve the items in the collection.
     *
     * This method is abstract and should be implemented in any class using this trait.
     *
     * @return array The items in the collection.
     */
    abstract protected function getItems(): array;

    /**
     * Sets the items in the collection.
     *
     * This method is abstract and should be implemented in any class using this trait.
     *
     * @param  array  $items  The items to set in the collection.
     */
    abstract protected function setItems(array $items): static;
}
