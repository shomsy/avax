<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Persistence;

use Avax\Components\DataStack\Persistence\System\Capabilities\IdentityMap\IdentityMap;
use Avax\Components\DataStack\Persistence\System\Capabilities\UnitOfWork\EntityPersisterInterface;
use Avax\Components\DataStack\Persistence\System\Capabilities\UnitOfWork\UnitOfWork;
use PHPUnit\Framework\TestCase;
use stdClass;

final class PersistenceCapabilitiesTest extends TestCase
{
    public function test_unit_of_work_tracks_new_entities() : void
    {
        $identityMap = new IdentityMap();
        $persister   = $this->createMock(EntityPersisterInterface::class);
        $uow         = new UnitOfWork($identityMap, $persister);

        $entity = new stdClass();
        $persister->expects($this->once())
            ->method('extractIdentifier')
            ->with($entity)
            ->willReturn(null);

        $uow->persist($entity);

        $this->assertTrue($uow->hasPendingChanges());
        $this->assertSame(1, $uow->pendingSummary()['new']);
    }

    public function test_unit_of_work_flushes_new_entities() : void
    {
        $identityMap = new IdentityMap();
        $persister   = $this->createMock(EntityPersisterInterface::class);
        $uow         = new UnitOfWork($identityMap, $persister);

        $entity = new stdClass();
        $persister->method('extractIdentifier')
            ->willReturnOnConsecutiveCalls(null, 123); // First check, then after insert

        $persister->expects($this->once())
            ->method('insert')
            ->with($entity);

        $uow->persist($entity);
        $uow->flush();

        $this->assertFalse($uow->hasPendingChanges());
        $this->assertTrue($identityMap->has($entity::class, 123));
        $this->assertSame($entity, $identityMap->get($entity::class, 123));
    }

    public function test_unit_of_work_tracks_and_flushes_removals() : void
    {
        $identityMap = new IdentityMap();
        $persister   = $this->createMock(EntityPersisterInterface::class);
        $uow         = new UnitOfWork($identityMap, $persister);

        $entity = new stdClass();
        $persister->method('extractIdentifier')->willReturn(456);
        $identityMap->put($entity::class, 456, $entity);

        $uow->remove($entity);

        $this->assertTrue($uow->hasPendingChanges());
        $this->assertSame(1, $uow->pendingSummary()['removed']);

        $persister->expects($this->once())
            ->method('delete')
            ->with($entity);

        $uow->flush();

        $this->assertFalse($uow->hasPendingChanges());
        $this->assertFalse($identityMap->has($entity::class, 456));
    }
}
