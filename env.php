<?php

/** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

use Dotenv\Dotenv;

if (! function_exists('env')) {
    /**
     * Retrieves the value of an environment variable.
     *
     * This function fetches the value of the specified environment variable. If the variable
     * is not set, it returns the provided default value.
     *
     * @param string $key     The name of the environment variable.
     * @param mixed  $default The default value to return if the environment variable is not set.
     *
     * @return mixed The value of the environment variable or the default value if not set.
     */
    function env(string $key, mixed $default = null) : mixed
    {
        $value = getenv(name: $key);

        if ($value === false) {
            return $default;
        }

        return $value;
    }
}

/**
 * Loads an environment configuration file and throws an exception if the file does not exist.
 *
 * @param string $filePath The path to the .env file to load.
 * @param string $context  The context of the .env file (used in error messages).
 *
 * @throws Exception If the .env file does not exist.
 */
function loadEnvFile(string $filePath, string $context) : void
{
    if (! file_exists($filePath)) {
        throw new Exception(
            message: sprintf(
                'The required %s .env file is missing at path: %s. 
                Please contact the developers to obtain the necessary file.',
                $context,
                $filePath,
            ),
        );
    }

    $dotenv = Dotenv::createImmutable(paths: dirname($filePath));
    $dotenv->load();
}

/**
 * Loads all environment configuration files into the application.
 *
 * This method will load both the global (root) .env file and any Docker-specific .env files
 * into the application. It prioritizes Docker-specific environment configurations if available.
 *
 * @throws Exception If any required .env file is missing.
 */
function loadEnvFiles() : void
{
    /* Load the root environment file */
    loadEnvFile(filePath: __DIR__ . '/.env', context: 'root');

    /* Load Docker-specific environment variables */
    $dockerEnv = __DIR__ . '/docker/mysql/.env';
    if (file_exists($dockerEnv)) {
        loadEnvFile(filePath: $dockerEnv, context: 'Docker MySQL');
    }
}

/* Execute Load of the environment files */
try {
    loadEnvFiles();
} catch (Throwable $throwable) {
    throw new Exception(message: $throwable->getMessage(), code: $throwable->getCode(), previous: $throwable);
}
