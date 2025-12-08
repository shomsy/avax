<?php

declare(strict_types=1);

namespace Avax\Auth\Contracts;

/**
 * PermissionInterface defines a contract for permission-related functionalities.
 * Any class implementing this interface must provide a string representation of a permission.
 */
interface PermissionInterface
{
    /**
     * Retrieve the string representation of a permission.
     *
     * The intention behind this method is to ensure consistent access and representation of permissions
     * across the application, which can be essential for authorization mechanisms.
     *
     * @return string The permission string.
     */
    public function getPermission() : string;
}
