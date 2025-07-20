<?php

declare(strict_types=1);

namespace Gemini\Database\QueryBuilder\Traits;

use Gemini\Database\QueryBuilder\Exception\QueryBuilderException;
use Gemini\Database\QueryBuilder\UnitOfWork;

/**
 * Trait ProvidesUnitOfWork
 *
 * Ensures that any class using this trait has access to a UnitOfWork instance.
 */
trait ProvidesUnitOfWork
{
    /**
     * Retrieves the UnitOfWork instance.
     *
     * @return UnitOfWork The Unit of Work instance.
     * @throws QueryBuilderException
     */
    protected function getUnitOfWork() : UnitOfWork
    {
        if (! isset($this->unitOfWork) || ! $this->unitOfWork instanceof UnitOfWork) {
            // Check if the `unitOfWork` property is either not set or is not an instance of the `UnitOfWork` class.
            // If this condition is true, throw a `QueryBuilderException` with a descriptive message.
            throw new QueryBuilderException(message: "UnitOfWork is not set in the class using this trait.");
        }
        if (! method_exists($this, 'getTableName')) {
            // Check if the class using this trait does not define the `getTableName` method.
            // If the `getTableName` method doesn't exist, throw a `QueryBuilderException` with an appropriate message.
            throw new QueryBuilderException(message: "getTableName() is not set in the class using this trait.");
        }

        // If both checks pass (i.e., `unitOfWork` is set and is an instance of `UnitOfWork`, and `getTableName()` exists),
        // return the `unitOfWork` property, which is expected to handle database query registration and execution.
        return $this->unitOfWork;
    }
}
