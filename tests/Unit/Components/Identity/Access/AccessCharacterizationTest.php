<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Access;

use Avax\Components\Application\Container\System\Foundation\SimpleContainer;
use Avax\Components\Identity\Access\System\Capabilities\Authorization\AuthorizationEngine;
use Avax\Components\Identity\Access\System\Capabilities\AdminElevation\AdminElevationStore;
use Avax\Components\Identity\Access\System\Configuration\AccessServiceProvider;
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

    private BeginAdminElevation $beginAdminElevation;

    private EndAdminElevation $endAdminElevation;

    #[Override]
    protected function setUp(): void
    {
        $this->beginAdminElevation = new BeginAdminElevation(store: new AdminElevationStore());
        $this->endAdminElevation = new EndAdminElevation(
            beginAdminElevation: $this->beginAdminElevation,
        );
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
        $access = $this->makeAccess(
            allowsResult: false,
            beginAdminElevation: $this->beginAdminElevation,
        );
        $this->beginAdminElevation->execute();

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
        self::assertFalse($this->beginAdminElevation->isActive());

        $this->beginAdminElevation->execute();
        self::assertTrue($this->beginAdminElevation->isActive());

        $this->endAdminElevation->execute();
        self::assertFalse($this->beginAdminElevation->isActive());
    }

    #[Test]
    public function elevationDoesNotLeakAcrossInstances(): void
    {
        $beginA = new BeginAdminElevation(store: new AdminElevationStore());
        $accessA = $this->makeAccess(allowsResult: false, beginAdminElevation: $beginA);
        $beginB = new BeginAdminElevation(store: new AdminElevationStore());
        $accessB = $this->makeAccess(allowsResult: false, beginAdminElevation: $beginB);

        $beginA->execute();

        self::assertTrue($accessA->allows('admin.only'));
        self::assertFalse($accessB->allows('admin.only'));
    }

    #[Test]
    public function elevationLifecycleComplete(): void
    {
        $begin = new BeginAdminElevation(store: new AdminElevationStore());
        $end = new EndAdminElevation(beginAdminElevation: $begin);
        $access = $this->makeAccess(allowsResult: false, beginAdminElevation: $begin);

        self::assertFalse($access->isElevated());
        self::assertFalse($access->allows('admin.only'));

        $begin->execute();
        self::assertTrue($access->isElevated());
        self::assertTrue($access->allows('admin.only'));

        $end->execute();
        self::assertFalse($access->isElevated());
        self::assertFalse($access->allows('admin.only'));
    }

    #[Test]
    public function providerResolvedAccessSurfacesDoNotShareElevationState(): void
    {
        $container = new SimpleContainer();
        (new AccessServiceProvider())->register($container);

        $firstAccess = $container->get(Access::class);
        $firstAccess->beginElevation();

        $secondAccess = $container->get(Access::class);

        self::assertTrue($firstAccess->isElevated());
        self::assertFalse($secondAccess->isElevated());
    }

    #[Test]
    public function requiresPublicSurfaceMethodsExist(): void
    {
        $access = $this->makeAccess(allowsResult: false);
        $access->requireAuthentication();
        $access->requireRole(\Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole::ADMIN);
        $access->requirePermission(new \Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserPermission(value: 'test'));
        self::expectNotToPerformAssertions();
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

    private function makeAccess(bool $allowsResult, ?BeginAdminElevation $beginAdminElevation = null): Access
    {
        $engine = new AuthorizationEngine(
            permissions: $allowsResult ? ['*'] : [],
            defaultAllow: $allowsResult,
        );
        $begin = $beginAdminElevation ?? new BeginAdminElevation(store: new AdminElevationStore());
        return new Access(
            authorizationEngine: $engine,
            beginAdminElevation: $begin,
            endAdminElevation: new EndAdminElevation(
                beginAdminElevation: $begin,
            ),
        );
    }
}
