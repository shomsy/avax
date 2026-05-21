<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Access;

use Avax\Components\Identity\Access\System\Capabilities\Authorization\AuthorizationEngine;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\BeginAdminElevation;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\EndAdminElevation;
use Avax\Components\Identity\Access\System\Foundation\Exception\PermissionDenied;
use Avax\Components\Identity\Access\System\PublicSurface\Access;
use Avax\Components\Identity\Access\System\PublicSurface\AccessInterface;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class AccessCharacterizationTest extends TestCase
{
    private AuthorizationEngine $engine;

    #[Override]
    protected function setUp(): void
    {
        BeginAdminElevation::reset();
    }

    #[Test]
    public function classIsFinalReadonly(): void
    {
        $reflection = new ReflectionClass(Access::class);
        self::assertTrue($reflection->isFinal(), 'Access must be final');
    }

    #[Test]
    public function implementsAccessInterface(): void
    {
        $reflection = new ReflectionClass(Access::class);
        self::assertTrue($reflection->implementsInterface(AccessInterface::class));
    }

    #[Test]
    public function methodSignaturesMatchInterface(): void
    {
        $ifaceMethods = (new ReflectionClass(AccessInterface::class))->getMethods();
        $classReflection = new ReflectionClass(Access::class);

        foreach ($ifaceMethods as $ifaceMethod) {
            $name = $ifaceMethod->getName();
            $classMethod = $classReflection->getMethod($name);
            self::assertSame(
                (string) $ifaceMethod->getReturnType(),
                (string) $classMethod->getReturnType(),
                "Access::{$name}() return type must match AccessInterface",
            );
        }
    }

    #[Test]
    public function allowsReturnsTrueWhenElevated(): void
    {
        $access = $this->makeAccess(allowsResult: false);
        (new BeginAdminElevation())->execute();

        self::assertTrue($access->allows('some.permission'));
    }

    #[Test]
    public function allowsDelegatesToEngineWhenNotElevated(): void
    {
        $access = $this->makeAccess(allowsResult: true);
        self::assertTrue($access->allows('read.post'));
    }

    #[Test]
    public function allowsReturnsFalseWhenEngineDenies(): void
    {
        $access = $this->makeAccess(allowsResult: false);
        self::assertFalse($access->allows('delete.admin'));
    }

    #[Test]
    public function deniesIsInverseOfAllows(): void
    {
        $access = $this->makeAccess(allowsResult: true);
        self::assertFalse($access->denies('read.post'));
    }

    #[Test]
    public function authorizeThrowsOnDeny(): void
    {
        $this->expectException(PermissionDenied::class);
        $access = $this->makeAccess(allowsResult: false);
        $access->authorize('delete.admin');
    }

    #[Test]
    public function authorizePassesOnAllow(): void
    {
        $access = $this->makeAccess(allowsResult: true);
        $access->authorize('read.post');
        self::assertTrue(true);
    }

    #[Test]
    public function elevationFlowChangesIsElevated(): void
    {
        $begin = new BeginAdminElevation();
        $end = new EndAdminElevation();

        self::assertFalse(BeginAdminElevation::active());

        $begin->execute();
        self::assertTrue(BeginAdminElevation::active());

        $end->execute();
        self::assertFalse(BeginAdminElevation::active());
    }

    #[Test]
    public function publicSurfaceDoesNotExposeInternalTypes(): void
    {
        $reflection = new ReflectionClass(Access::class);
        $internalTypes = [
            'AuthorizationEngine',
            'BeginAdminElevation',
            'EndAdminElevation',
        ];

        foreach ($reflection->getMethods() as $method) {
            $returnType = $method->getReturnType();
            if ($returnType === null) {
                continue;
            }
            $returnName = $returnType instanceof \ReflectionNamedType ? $returnType->getName() : '';
            $shortName = explode('\\', $returnName);
            $shortName = end($shortName);
            foreach ($internalTypes as $internalType) {
                self::assertNotSame(
                    $internalType,
                    $shortName,
                    "Access::{$method->getName()}() should not expose internal type {$internalType}",
                );
            }
        }
    }

    private function makeAccess(bool $allowsResult): Access
    {
        $engine = new AuthorizationEngine(
            permissions: $allowsResult ? ['*'] : [],
            defaultAllow: $allowsResult,
        );
        return new Access(
            authorizationEngine: $engine,
            beginAdminElevation: new BeginAdminElevation(),
            endAdminElevation: new EndAdminElevation(),
        );
    }
}
