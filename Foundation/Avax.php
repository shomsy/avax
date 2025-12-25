<?php

declare(strict_types=1);

namespace Avax;

use RuntimeException;

/**
 * Enum Avax
 *
 * Manages application paths specifically within the "Foundation" directory of the project.
 * Provides dynamic resolution and validation for key Foundation components.
 */
enum Avax: string
{
    // Foundation core directories
    case AUTH          = 'Foundation/Auth/';

    case CACHE         = 'Foundation/Cache/';

    case CONFIG        = 'Foundation/Config/';

    case CONTAINER     = 'Foundation/Container/';

    case DATA_HANDLING = 'Foundation/DataHandling/';

    case DATABASE      = 'Foundation/Database/';

    case EXCEPTIONS    = 'Foundation/Exceptions/';

    case FACADE        = 'Foundation/Facade/';

    case FILESYSTEM    = 'Foundation/Filesystem/';

    case HTTP          = 'Foundation/HTTP/';

    case LOGGING       = 'Foundation/Logging/';

    case MIDDLEWARES   = 'Foundation/Middlewares/';

    case VIEW          = 'Foundation/View/';

    case MIGRATIONS    = 'Foundation/Database/Migration/';


    /**
     * Retrieve the list of all paths managed by this enum.
     *
     * @return array An associative array of enum cases and their resolved paths.
     */
    public static function all() : array
    {
        return array_map(callback: fn(Avax $Avax) : string => $Avax->resolve(), array: self::cases());
    }

    /**
     * Resolves the full path by appending the root directory to the relative path.
     *
     * @return string The resolved absolute path.
     */
    public function resolve() : string
    {
        $path = self::root() . $this->value;

        if (! file_exists(filename: $path)) {
            throw new RuntimeException(message: sprintf('The path "%s" does not exist.', $path));
        }

        return $path;
    }

    /**
     *
     */
    public static function root() : string
    {
        // Ensure APP_ROOT is set correctly in the environment.
        $root = env(key: 'FW_ROOT', default: dirname(path: __DIR__, levels: 2));

        if (! is_dir(filename: $root)) {
            throw new RuntimeException(message: "The root path '" . $root . "' does not exist.");
        }

        return rtrim(string: (string) $root, characters: '/') . '/Infrastructure/';
    }

}
