<?php

declare(strict_types=1);

namespace Avax\Components\Application\Config\System\Capabilities\Architecture;

/**
 * Enum AppPath.
 *
 * Manages various important directory paths within the application.
 * Migrated to Config component per refactor.md.
 */
enum AppPath: string
{
    case VIEW_CACHE_PATH = 'storage/views';
    case LOGS_PATH = 'storage/logs/';
    case CONFIG = 'Config';
    case AUTOLOAD_PATH = 'vendor/autoload.php';
    case HELPERS_PATH = 'Foundation/Helpers/helpers.php';
    case ROUTES_PATH = 'Presentation/HTTP/routes/';
    case MIGRATIONS_PATH = 'Infrastructure/migrations';
    case DTO_PATH = 'Application/DTO';
    case ROUTE_CACHE_PATH = 'storage/cache/routes.cache.php';
    case STUBS_PATH = 'Infrastructure/Foundation/Database/Migration/Runner/Stubs/';

    public function get(): string
    {
        return self::getRoot().$this->value;
    }

    public static function getRoot(): string
    {
        $currentDir = __DIR__;
        $composerFile = 'composer.json';
        $rootDir = '/';

        while (! file_exists($currentDir.DIRECTORY_SEPARATOR.$composerFile)) {
            $currentDir = dirname($currentDir);
            if ($currentDir === $rootDir) {
                // Fallback for some environments where composer.json might be 2-3 levels up
                return realpath(__DIR__.'/../../../../../../').DIRECTORY_SEPARATOR;
            }
        }

        return $currentDir.DIRECTORY_SEPARATOR;
    }
}
