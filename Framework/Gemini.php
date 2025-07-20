<?php

declare(strict_types=1);

namespace Gemini;

use RuntimeException;

/**
 * Enum Gemini
 *
 * Manages application paths specifically within the "Framework" directory of the project.
 * Provides dynamic resolution and validation for key framework components.
 */
enum Gemini: string
{
    // Framework core directories
    case AUTH          = 'Framework/Auth/';

    case CACHE         = 'Framework/Cache/';

    case CONFIG        = 'Framework/Config/';

    case CONTAINER     = 'Framework/Container/';

    case DATA_HANDLING = 'Framework/DataHandling/';

    case DATABASE      = 'Framework/Database/';

    case EXCEPTIONS    = 'Framework/Exceptions/';

    case FACADE        = 'Framework/Facade/';

    case FILESYSTEM    = 'Framework/Filesystem/';

    case HTTP          = 'Framework/HTTP/';

    case LOGGING       = 'Framework/Logging/';

    case MIDDLEWARES   = 'Framework/Middlewares/';

    case VIEW          = 'Framework/View/';

    case MIGRATIONS    = 'Framework/Database/Migration/';


    /**
     * Retrieve the list of all paths managed by this enum.
     *
     * @return array An associative array of enum cases and their resolved paths.
     */
    public static function all() : array
    {
        return array_map(callback: fn(Gemini $gemini) : string => $gemini->resolve(), array: self::cases());
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
        $root = env(key: 'FW_ROOT', default: dirname(__DIR__, 2));

        if (! is_dir($root)) {
            throw new RuntimeException(message: "The root path '" . $root . "' does not exist.");
        }

        return rtrim((string) $root, '/') . '/Infrastructure/';
    }

}
