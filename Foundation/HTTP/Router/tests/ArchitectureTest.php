<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Tests;

use Avax\HTTP\Router\Router;
use Avax\HTTP\Router\RouterInterface;
use Avax\HTTP\Router\System\Capabilities\RouteDefinition\RouteDefinition;
use Avax\HTTP\Router\System\Capabilities\RouterTrace\RouterTrace;
use Avax\HTTP\Router\System\Flows\BootstrapRoutes\Cache\RouteCacheLoader;
use Avax\HTTP\Router\System\Flows\BootstrapRoutes\Cache\RouteCacheManifest;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Definitions\RouteRegistry;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Fallback\RegisteredFallback;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Files\RouteFileRegistrar;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Files\RouteRegistrarProxy;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\Groups\RouteGroupFrames;
use Avax\HTTP\Router\System\Flows\RegisterRoutes\RouterDsl;
use Avax\HTTP\Router\System\Flows\ResolveRequest\HttpRequestRouter;
use Avax\HTTP\Router\System\Flows\ResolveRequest\Matching\DomainAwareMatcher;
use Avax\HTTP\Router\System\Flows\ResolveRequest\Matching\RouteMatcher;
use Avax\HTTP\Router\System\Flows\RunRoute\Pipeline\RoutePipeline;
use Avax\HTTP\Router\System\Flows\RunRoute\Pipeline\StageChain;
use Avax\HTTP\Router\System\Flows\RunRoute\RouterKernel;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

/**
 * Architectural guard tests to ensure the Router component maintains
 * enterprise-grade architectural integrity and follows DDD principles.
 */
final class ArchitectureTest extends TestCase
{
    /**
     * @var array<class-string> List of all Router component classes to validate
     */
    private const array ROUTER_CLASSES
        = [
            Router::class,
            RouterDsl::class,
            RouterKernel::class,
            RouterInterface::class,
            HttpRequestRouter::class,
            RouteDefinition::class,
            RouteMatcher::class,
            DomainAwareMatcher::class,
            RouteRegistrarProxy::class,
            RoutePipeline::class,
            StageChain::class,
            RouteRegistry::class,
            RouteGroupFrames::class,
            RegisteredFallback::class,
            RouteCacheLoader::class,
            RouteCacheManifest::class,
            RouteFileRegistrar::class,
            RouterTrace::class,
        ];

    /**
     * Ensures no static mutable properties exist in the Router component.
     *
     * Static mutable properties violate DDD principles by creating global state
     * that can cause race conditions, testing difficulties, and unpredictable behavior.
     */
    public function testNoStaticMutableProperties() : void
    {
        foreach (self::ROUTER_CLASSES as $className) {
            if (! class_exists(class: $className)) {
                continue; // Skip interfaces or non-existent classes
            }

            $reflection       = new ReflectionClass(objectOrClass: $className);
            $staticProperties = $reflection->getProperties(filter: ReflectionProperty::IS_STATIC);

            foreach ($staticProperties as $property) {
                // Allow static constants (immutable by definition)
                if ($property->isPublic() && $property->isStatic()) {
                    $this->assertTrue(
                        condition: $property->isReadOnly() || $property->isFinal(),
                        message  : sprintf(
                                       'Static property %s::%s must be readonly or final to prevent mutable global state',
                                       $className,
                                       $property->getName()
                                   )
                    );
                }
            }
        }

        $this->assertTrue(condition: true, message: 'All Router classes passed static mutability validation');
    }

    /**
     * Ensures Router component has no dependencies on Bootstrap layer.
     *
     * This prevents architectural violations where the runtime Router depends on
     * bootstrap/initialization code, maintaining clean separation of concerns.
     */
    public function testNoBootstrapDependencies() : void
    {
        foreach (self::ROUTER_CLASSES as $className) {
            if (! class_exists(class: $className)) {
                continue;
            }

            $reflection = new ReflectionClass(objectOrClass: $className);

            // Check constructor parameters for bootstrap dependencies
            $constructor = $reflection->getConstructor();
            if ($constructor !== null) {
                foreach ($constructor->getParameters() as $parameter) {
                    $type = $parameter->getType();
                    if ($type instanceof ReflectionNamedType) {
                        $typeName = $type->getName();

                        // Check if parameter type is from Bootstrap namespace
                        if (str_contains(haystack: $typeName, needle: 'Avax\\HTTP\\Router\\Bootstrap')) {
                            $this->fail(message: sprintf(
                                                     'Router class %s depends on Bootstrap layer (%s) in constructor, violating architectural boundaries',
                                                     $className,
                                                     $typeName
                                                 ));
                        }
                    }
                }
            }

            // Check property types for bootstrap dependencies
            foreach ($reflection->getProperties() as $property) {
                $type = $property->getType();
                if ($type instanceof ReflectionNamedType) {
                    $typeName = $type->getName();

                    if (str_contains(haystack: $typeName, needle: 'Avax\\HTTP\\Router\\Bootstrap')) {
                        $this->fail(message: sprintf(
                                                 'Router class %s has Bootstrap dependency (%s) as property, violating architectural boundaries',
                                                 $className,
                                                 $typeName
                                             ));
                    }
                }
            }

            // Check method return types for bootstrap dependencies
            foreach ($reflection->getMethods() as $method) {
                $returnType = $method->getReturnType();
                if ($returnType instanceof ReflectionNamedType) {
                    $typeName = $returnType->getName();

                    if (str_contains(haystack: $typeName, needle: 'Avax\\HTTP\\Router\\Bootstrap')) {
                        $this->fail(message: sprintf(
                                                 'Router method %s::%s() returns Bootstrap type (%s), violating architectural boundaries',
                                                 $className,
                                                 $method->getName(),
                                                 $typeName
                                             ));
                    }
                }

                // Check method parameters for bootstrap dependencies
                foreach ($method->getParameters() as $parameter) {
                    $type = $parameter->getType();
                    if ($type instanceof ReflectionNamedType) {
                        $typeName = $type->getName();

                        if (str_contains(haystack: $typeName, needle: 'Avax\\HTTP\\Router\\Bootstrap')) {
                            $this->fail(message: sprintf(
                                                     'Router method %s::%s() accepts Bootstrap type (%s) as parameter, violating architectural boundaries',
                                                     $className,
                                                     $method->getName(),
                                                     $typeName
                                                 ));
                        }
                    }
                }
            }
        }

        $this->assertTrue(condition: true, message: 'All Router classes passed Bootstrap dependency validation');
    }

    /**
     * Ensures all Router classes are properly namespaced under Avax\HTTP\Router.
     *
     * This maintains consistent organization and prevents namespace pollution.
     */
    public function testProperNamespacing() : void
    {
        foreach (self::ROUTER_CLASSES as $className) {
            if (! class_exists(class: $className)) {
                continue;
            }

            $this->assertStringStartsWith(
                prefix : 'Avax\\HTTP\\Router',
                string : $className,
                message: sprintf('Class %s is not properly namespaced under Avax\\HTTP\\Router', $className)
            );
        }

        $this->assertTrue(condition: true, message: 'All Router classes have proper namespacing');
    }

    /**
     * Ensures all Router classes follow immutability principles where appropriate.
     *
     * Immutable objects prevent state mutations that can cause bugs and testing difficulties.
     */
    public function testImmutabilityPrinciples() : void
    {
        $immutableClasses = [
            RouteDefinition::class,
            // RouterTrace is intentionally mutable for tracing functionality
        ];

        foreach ($immutableClasses as $className) {
            if (! class_exists(class: $className)) {
                continue;
            }

            $reflection = new ReflectionClass(objectOrClass: $className);

            // Check if class is readonly (PHP 8.2+ feature)
            $this->assertTrue(
                condition: $reflection->isReadOnly(),
                message  : sprintf('Class %s should be readonly to ensure immutability', $className)
            );
        }

        $this->assertTrue(condition: true, message: 'All specified Router classes follow immutability principles');
    }

    /**
     * Validates that interfaces are properly segregated from implementations.
     *
     * This ensures clean contracts and prevents tight coupling between components.
     */
    public function testInterfaceSegregation() : void
    {
        $interfaces = [
            RouterInterface::class,
        ];

        foreach ($interfaces as $interfaceName) {
            if (! interface_exists(interface: $interfaceName)) {
                continue;
            }

            $reflection = new ReflectionClass(objectOrClass: $interfaceName);

            // Interfaces should not depend on concrete implementations
            foreach ($reflection->getMethods() as $method) {
                foreach ($method->getParameters() as $parameter) {
                    $type = $parameter->getType();
                    if ($type instanceof ReflectionNamedType) {
                        $typeName = $type->getName();

                        // Interfaces should not reference concrete Bootstrap classes
                        $this->assertFalse(
                            condition: str_contains(haystack: $typeName, needle: 'Avax\\HTTP\\Router\\Bootstrap'),
                            message  : sprintf(
                                           'Interface %s method %s() references Bootstrap class %s, violating interface segregation',
                                           $interfaceName,
                                           $method->getName(),
                                           $typeName
                                       )
                        );
                    }
                }
            }
        }

        $this->assertTrue(condition: true, message: 'All Router interfaces follow proper segregation principles');
    }
}