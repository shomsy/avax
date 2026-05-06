<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\FeatureFlags;

use Avax\Components\Application\FeatureFlags\System\Capabilities\Flags\InMemoryFlagStore;
use Avax\Components\Application\FeatureFlags\System\PublicSurface\FeatureFlags;
use Avax\Components\Application\FeatureFlags\System\PublicSurface\FlagStoreInterface;
use PHPUnit\Framework\TestCase;

final class FeatureFlagsTest extends TestCase
{
    public function test_missing_flag_is_disabled_by_default() : void
    {
        $this->assertFalse(FeatureFlags::enabled('missing_flag'));
        $this->assertSame('', FeatureFlags::variant('missing_flag'));
    }

    public function test_enable_and_disable_update_flag_state() : void
    {
        FeatureFlags::enable('new_dashboard');

        $this->assertTrue(FeatureFlags::enabled('new_dashboard'));

        FeatureFlags::disable('new_dashboard');

        $this->assertFalse(FeatureFlags::enabled('new_dashboard'));
    }

    public function test_custom_store_overrides_flag_values() : void
    {
        FeatureFlags::setStore(new InMemoryFlagStore([
                                                         'rollout'    => 'variant-b',
                                                         'forced_on'  => '1',
                                                         'forced_off' => '0',
                                                     ]));

        $this->assertSame('variant-b', FeatureFlags::variant('rollout'));
        $this->assertTrue(FeatureFlags::enabled('forced_on'));
        $this->assertFalse(FeatureFlags::enabled('forced_off'));
    }

    public function test_all_returns_current_store_snapshot() : void
    {
        FeatureFlags::setStore(new InMemoryFlagStore([
                                                         'alpha'   => true,
                                                         'variant' => 'blue',
                                                     ]));

        $this->assertSame([
                              'alpha' => true,
                                                                                                                                 'variant' => 'blue',
                          ], FeatureFlags::all());
    }

    public function test_public_surface_accepts_any_flag_store_contract() : void
    {
        FeatureFlags::setStore(new class implements FlagStoreInterface {
            /** @var array<string, bool> */
            private array $flags
                = [
                    'contract_store' => true,
                ];

            public function get(string $flag) : mixed
            {
                return $this->flags[$flag] ?? false;
            }

            public function set(string $flag, mixed $value) : void
            {
                $this->flags[$flag] = $value;
            }

            /** @return array<string, bool> */
            public function all() : array
            {
                return $this->flags;
            }
        });

        $this->assertTrue(FeatureFlags::enabled('contract_store'));
    }

    protected function setUp() : void
    {
        FeatureFlags::setStore(new InMemoryFlagStore());
    }
}
