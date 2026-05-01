<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Session\Flows;

use Avax\Components\HTTP\Session\System\Capabilities\Storage\ArraySessionStore;
use Avax\Components\HTTP\Session\System\Flows\ReadSessionValue\ReadSessionValue;
use Avax\Components\HTTP\Session\System\Flows\StoreSessionValue\StoreSessionValue;
use Avax\Components\HTTP\Session\System\PublicSurface\SessionScope;
use Avax\Tests\TestCase;

final class ReadWriteFlowTest extends TestCase
{
    private function createScope() : SessionScope
    {
        return new SessionScope(new ArraySessionStore);
    }

    public function test_read_value_from_scope() : void
    {
        $scope = $this->createScope();
        $scope->set('user_id', 42);

        $reader = new ReadSessionValue($scope);
        $result = $reader->execute('user_id');

        $this->assertSame(42, $result);
    }

    public function test_write_value_to_scope() : void
    {
        $scope = $this->createScope();

        $writer = new StoreSessionValue($scope);
        $writer->execute('user_id', 42);

        $this->assertSame(42, $scope->get('user_id'));
    }

    public function test_read_with_default() : void
    {
        $scope = $this->createScope();

        $reader = new ReadSessionValue($scope);
        $result = $reader->execute('missing', 'default');

        $this->assertSame('default', $result);
    }
}
