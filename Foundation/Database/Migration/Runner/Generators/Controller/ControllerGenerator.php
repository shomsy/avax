<?php

declare(strict_types=1);

namespace Avax\Database\Migration\Runner\Generators\Controller;

use Avax\Database\Migration\Runner\Generators\AbstractGenerator;
use RuntimeException;

/**
 * ControllerGenerator Class
 *
 * This final class is responsible for generating RESTful controllers based on a given name.
 * It inherits from AbstractGenerator, ensuring reusable code for common generator functionalities.
 */
final class ControllerGenerator extends AbstractGenerator
{
    /**
     * Creates a RESTful controller class file.
     *
     * @param string $name The name of the controller to be generated.
     *
     * This method constructs a namespace and path for controller files using
     * configuration variables. If these configurations are missing, it throws an exception.
     * The class name is generated, and a stub file is loaded and customized with placeholders.
     * Finally, the customized stub is written to the appropriate file path.
     *
     * @throws RuntimeException If the namespace or path configuration is missing, or if file operations fail.
     */
    public function create(string $name) : void
    {
        // Retrieve the namespace and path for controllers from the configuration
        $namespace = config(key: 'app.namespaces.Controllers');
        $path      = config(key: 'app.paths.Controllers');

        // Ensure both namespace and path are configured
        if ($namespace === null || $path === null) {
            throw new RuntimeException(message: 'Controllers namespace or path is not configured.');
        }

        // Generate the class name for the controller
        $className = $this->generateMigrationClassName(tableName: $name, type: 'controller');

        // Load the controller stub and replace placeholders
        $stub = $this->getStub(stubName: 'controller.stub');
        $stub = $this->replacePlaceholders(stub: $stub, placeholders: [
            'ControllerName' => $className,
            'namespace'      => $namespace,
        ]);

        // Resolve the file path for the new controller and write the customized stub content
        $destinationPath = $this->resolvePath(namespace: $namespace, name: $className);
        $this->writeToFile(path: $destinationPath, content: $stub);
    }
}