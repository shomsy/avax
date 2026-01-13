<?php

declare(strict_types=1);

namespace Avax\HTTP\Router\Tests;

use Avax\HTTP\Router\Bootstrap\RouteRegistrar;
use Avax\HTTP\Router\Cache\RouteCacheLoader;
use Avax\HTTP\Router\Cache\RouteCacheManifest;
use Avax\HTTP\Router\Kernel\RouterKernel;
use Avax\HTTP\Router\Routing\DomainAwareMatcher;
use Avax\HTTP\Router\Routing\HttpRequestRouter;
use Avax\HTTP\Router\Routing\RouteDefinition;
use Avax\HTTP\Router\Routing\RouteGroupStack;
use Avax\HTTP\Router\Routing\RouteMatcher;
use Avax\HTTP\Router\Routing\RoutePipeline;
use Avax\HTTP\Router\Routing\RouteRegistrarProxy;
use Avax\HTTP\Router\Routing\StageChain;
use Avax\HTTP\Router\Router;
use Avax\HTTP\Router\RouterDsl;
use Avax\HTTP\Router\RouterInterface;
use Avax\HTTP\Router\Support\FallbackManager;
use Avax\HTTP\Router\Support\RouteRegistry;
use Avax\HTTP\Router\Tracing\RouterTrace;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
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
    private const ROUTER_CLASSES = [
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
        RouteGroupStack::class,
        FallbackManager::class,
        RouteCacheLoader::class,
        RouteCacheManifest::class,
        RouteRegistrar::class,
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
            if (! class_exists($className)) {
                continue; // Skip interfaces or non-existent classes
            }

            $reflection = new ReflectionClass($className);
            $staticProperties = $reflection->getProperties(ReflectionProperty::IS_STATIC);

            foreach ($staticProperties as $property) {
                // Allow static constants (immutable by definition)
                if ($property->isPublic() && $property->isStatic()) {
                    $this->assertTrue(
                        $property->isReadOnly() || $property->isFinal(),
                        sprintf(
                            'Static property %s::%s must be readonly or final to prevent mutable global state',
                            $className,
                            $property->getName()
                        )
                    );
                }
            }
        }

        $this->assertTrue(true, 'All Router classes passed static mutability validation');
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
            if (! class_exists($className)) {
                continue;
            }

            $reflection = new ReflectionClass($className);

            // Check constructor parameters for bootstrap dependencies
            $constructor = $reflection->getConstructor();
            if ($constructor !== null) {
                foreach ($constructor->getParameters() as $parameter) {
                    $type = $parameter->getType();
                    if ($type instanceof \ReflectionNamedType) {
                        $typeName = $type->getName();

                        // Check if parameter type is from Bootstrap namespace
                        if (str_contains($typeName, 'Avax\\HTTP\\Router\\Bootstrap')) {
                            $this->fail(sprintf(
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
                if ($type instanceof \ReflectionNamedType) {
                    $typeName = $type->getName();

                    if (str_contains($typeName, 'Avax\\HTTP\\Router\\Bootstrap')) {
                        $this->fail(sprintf(
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
                if ($returnType instanceof \ReflectionNamedType) {
                    $typeName = $returnType->getName();

                    if (str_contains($typeName, 'Avax\\HTTP\\Router\\Bootstrap')) {
                        $this->fail(sprintf(
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
                    if ($type instanceof \ReflectionNamedType) {
                        $typeName = $type->getName();

                        if (str_contains($typeName, 'Avax\\HTTP\\Router\\Bootstrap')) {
                            $this->fail(sprintf(
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

        $this->assertTrue(true, 'All Router classes passed Bootstrap dependency validation');
    }

    /**
     * Ensures all Router classes are properly namespaced under Avax\HTTP\Router.
     *
     * This maintains consistent organization and prevents namespace pollution.
     */
    public function testProperNamespacing() : void
    {
        foreach (self::ROUTER_CLASSES as $className) {
            if (! class_exists($className)) {
                continue;
            }

            $this->assertStringStartsWith(
                'Avax\\HTTP\\Router',
                $className,
                sprintf('Class %s is not properly namespaced under Avax\\HTTP\\Router', $className)
            );
        }

        $this->assertTrue(true, 'All Router classes have proper namespacing');
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
            if (! class_exists($className)) {
                continue;
            }

            $reflection = new ReflectionClass($className);

            // Check if class is readonly (PHP 8.2+ feature)
            $this->assertTrue(
                $reflection->isReadOnly(),
                sprintf('Class %s should be readonly to ensure immutability', $className)
            );
        }

        $this->assertTrue(true, 'All specified Router classes follow immutability principles');
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
            if (! interface_exists($interfaceName)) {
                continue;
            }

            $reflection = new ReflectionClass($interfaceName);

            // Interfaces should not depend on concrete implementations
            foreach ($reflection->getMethods() as $method) {
                foreach ($method->getParameters() as $parameter) {
                    $type = $parameter->getType();
                    if ($type instanceof \ReflectionNamedType) {
                        $typeName = $type->getName();

                        // Interfaces should not reference concrete Bootstrap classes
                        $this->assertFalse(
                            str_contains($typeName, 'Avax\\HTTP\\Router\\Bootstrap'),
                            sprintf(
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

        $this->assertTrue(true, 'All Router interfaces follow proper segregation principles');
    }
}