<?php

declare(strict_types=1);

namespace Gemini\Config\Architecture\DDD;

use RuntimeException;

/**
 * Enum AppPath
 *
 * This enum is responsible for managing various important directory paths
 * within the application. Each enum case represents a relative path to a
 * significant part of the framework or project. The enum provides a
 * dynamic method to resolve these paths based on the root directory of
 * the project, ensuring flexibility and maintainability.
 */
enum AppPath: string
{
    /**
     * The path to the view cache directory where compiled Blade views are stored.
     * This case represents the directory for storing cached view files, allowing
     * the application to load views more quickly on subsequent requests.
     */
    case VIEW_CACHE_PATH = 'storage/views';

    /**
     * This constant defines the path to the logs directory.
     *
     * The constant LOGS_PATH can be used throughout the application
     * whenever the logs directory path is needed, ensuring consistency.
     *
     * Example usage:
     * ```
     * $logFilePath = LOGS_PATH . 'error.log';
     * ```
     */
    case LOGS_PATH = 'storage/logs/';

    /**
     * The path to the configuration files within the project.
     * This case represents the location where all configuration files
     * related to the application are stored. The configuration files typically
     * include settings such as database configurations, service configurations,
     * and other environment-specific options.
     */
    case CONFIG = 'Infrastructure/Config';

    /**
     * The path to the Composer autoload file.
     * This case represents the path to Composer's autoload file, which is
     * essential for automatically loading classes in the project based on the
     * PSR-4 autoloading standard. This is typically located in the vendor directory.
     *
     */
    case AUTOLOAD_PATH = 'vendor/autoload.php'; // Removed the leading slash

    /**
     * The path to the helper functions used throughout the framework.
     * This case points to the helper functions, which are reusable, framework-agnostic
     * utilities that can be used globally within the application. These functions are
     * typically generic and assist in tasks such as formatting, array manipulation,
     * string handling, and debugging.
     */
    case HELPERS_PATH = 'Infrastructure/Framework/Helpers/helpers.php'; // Removed the leading slash

    /**
     * The path to the web routes file which defines the HTTP routes for the application.
     * This case refers to the file where all HTTP routes are defined, mapping
     * incoming web requests to their respective controllers and actions within the
     * application. This is essential for the routing system to function.
     */
    case ROUTES_PATH = 'Presentation/HTTP/routes/'; // Removed the leading slash

    /**
     * The path to the database migration files.
     * This case represents the directory where migration files are stored.
     * These migrations are responsible for defining the database schema changes
     * such as creating, modifying, or dropping tables. They allow for easy
     * version control of the database schema.
     */
    case MIGRATIONS_PATH = 'Infrastructure/migrations'; // Removed the leading slash

    /**
     * Constant representing the directory path for Data Transfer Objects (DTOs).
     *
     * DTOs are lightweight objects used to transfer data between layers in the system,
     * specifically designed to minimize coupling and improve separation of concerns.
     *
     * @var string DTO_PATH The relative path for storing application DTOs.
     */
    case DTO_PATH = 'Application/DTO'; // Removed the leading slash for project-relative paths

    /**
     * The path to the compiled route cache file.
     *
     * This path should point to a file where the precompiled routes
     * are dumped by the route compiler and later loaded at boot time.
     * This dramatically reduces routing overhead.
     */
    case ROUTE_CACHE_PATH = 'storage/cache/routes.cache.php';

    case STUBS_PATH       = 'Infrastructure/Framework/Database/Migration/Runner/Stubs/';

    /**
     * Get the full path by prepending the root directory to the enum case value.
     *
     * This method dynamically resolves the full path for each enum case by
     * prepending the project's root directory to the relative path defined
     * in the enum case. This allows the paths to be flexible and environment-independent.
     *
     * @return string The full absolute path based on the enum case.
     */
    public function get() : string
    {
        return self::getRoot() . $this->value; // Constructs the full path
    }

    /**
     * Dynamically determines the project's root directory.
     *
     * This method traverses up the directory hierarchy until it locates the `composer.json` file,
     * which serves as a reliable indicator of the project's root. This approach ensures that
     * the method remains flexible and independent of any hardcoded directory structure.
     *
     * If the root cannot be determined, an exception is thrown to prevent incorrect path resolution.
     *
     * @return string The absolute path to the project's root directory.
     * @throws RuntimeException If the project root cannot be found.
     */
    public static function getRoot() : string
    {
        $currentDir   = __DIR__; // Im here right now
        $composerFile = 'composer.json'; // looking for composer in the project
        $rootDir      = '/'; // should be root of (any) project

        // (1) Start from the current directory
        while (! file_exists($currentDir . DIRECTORY_SEPARATOR . $composerFile)) { // (2) Check if composer.json exists
            $currentDir = dirname($currentDir); // (3) Move one directory up

            if ($currentDir === $rootDir) { // (4) Prevent infinite loop if root is not found
                throw new RuntimeException(message: 'Project root not found');
            }
        }

        // (5) Return the root path
        return $currentDir . DIRECTORY_SEPARATOR;
    }

}
