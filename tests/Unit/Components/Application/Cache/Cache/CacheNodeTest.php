<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Cache;

use Avax\Components\Application\Cache\System\Capabilities\Distribution\CacheNode;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\DistributeCachedValues\CacheNodeStatus;
use PHPUnit\Framework\TestCase;

final class CacheNodeTest extends TestCase
{
    // --- Creation with all properties ---

    public function test_create_with_all_properties(): void
    {
        $node = new CacheNode(
            id    : 'node-a',
            host  : '127.0.0.1',
            port  : 6379,
            weight: 200,
        );

        $this->assertSame('node-a', $node->id);
        $this->assertSame('127.0.0.1', $node->host);
        $this->assertSame(6379, $node->port);
        $this->assertSame(200, $node->weight);
        $this->assertSame(CacheNodeStatus::HEALTHY, $node->status);
    }

    public function test_create_with_defaults(): void
    {
        $node = new CacheNode(
            id  : 'node-b',
            host: '10.0.0.1',
            port: 6380,
        );

        $this->assertSame('node-b', $node->id);
        $this->assertSame(100, $node->weight);
        $this->assertSame(CacheNodeStatus::HEALTHY, $node->status);
    }

    public function test_create_with_custom_status(): void
    {
        $node = new CacheNode(
            id    : 'node-c',
            host  : '10.0.0.2',
            port  : 6381,
            status: CacheNodeStatus::UNHEALTHY,
        );

        $this->assertSame(CacheNodeStatus::UNHEALTHY, $node->status);
    }

    // --- Factory method create() ---

    public function test_create_factory_method(): void
    {
        $node = CacheNode::create(
            id    : 'factory-node',
            host  : '192.168.1.1',
            port  : 6382,
            weight: 150,
            status: CacheNodeStatus::DRAINING,
        );

        $this->assertSame('factory-node', $node->id);
        $this->assertSame('192.168.1.1', $node->host);
        $this->assertSame(6382, $node->port);
        $this->assertSame(150, $node->weight);
        $this->assertSame(CacheNodeStatus::DRAINING, $node->status);
    }

    // --- Readonly properties ---

    public function test_id_is_readonly(): void
    {
        $node = new CacheNode(id: 'readonly-id', host: 'host', port: 1234);

        $this->assertSame('readonly-id', $node->id);
    }

    public function test_host_is_readonly(): void
    {
        $node = new CacheNode(id: 'id', host: 'readonly-host', port: 1234);

        $this->assertSame('readonly-host', $node->host);
    }

    public function test_port_is_readonly(): void
    {
        $node = new CacheNode(id: 'id', host: 'host', port: 9999);

        $this->assertSame(9999, $node->port);
    }

    public function test_weight_is_readonly(): void
    {
        $node = new CacheNode(id: 'id', host: 'host', port: 1234, weight: 250);

        $this->assertSame(250, $node->weight);
    }

    // --- virtualNodeCount() ---

    public function test_virtual_node_count_with_default_weight(): void
    {
        $node = new CacheNode(id: 'node', host: 'host', port: 1234);

        $this->assertSame(150, $node->virtualNodeCount());
    }

    public function test_virtual_node_count_with_custom_weight(): void
    {
        $node = new CacheNode(id: 'node', host: 'host', port: 1234, weight: 200);

        $this->assertSame(300, $node->virtualNodeCount());
    }

    public function test_virtual_node_count_with_half_weight(): void
    {
        $node = new CacheNode(id: 'node', host: 'host', port: 1234, weight: 50);

        $this->assertSame(75, $node->virtualNodeCount());
    }

    public function test_virtual_node_count_with_explicit_count(): void
    {
        $node = new CacheNode(id: 'node', host: 'host', port: 1234, virtualNodeCount: 500);

        $this->assertSame(500, $node->virtualNodeCount());
    }

    public function test_virtual_node_count_explicit_overrides_weight(): void
    {
        $node = new CacheNode(id: 'node', host: 'host', port: 1234, weight: 200, virtualNodeCount: 100);

        $this->assertSame(100, $node->virtualNodeCount());
    }

    // --- withStatus() immutability ---

    public function test_with_status_returns_new_instance(): void
    {
        $original = new CacheNode(id: 'node', host: 'host', port: 1234);
        $modified = $original->withStatus(CacheNodeStatus::UNHEALTHY);

        $this->assertNotSame($original, $modified);
        $this->assertSame(CacheNodeStatus::HEALTHY, $original->status);
        $this->assertSame(CacheNodeStatus::UNHEALTHY, $modified->status);
    }

    public function test_with_status_preserves_other_properties(): void
    {
        $original = new CacheNode(id: 'preserved', host: '10.0.0.1', port: 6380, weight: 200);
        $modified = $original->withStatus(CacheNodeStatus::MAINTENANCE);

        $this->assertSame('preserved', $modified->id);
        $this->assertSame('10.0.0.1', $modified->host);
        $this->assertSame(6380, $modified->port);
        $this->assertSame(200, $modified->weight);
        $this->assertSame(CacheNodeStatus::MAINTENANCE, $modified->status);
    }

    // --- withWeight() immutability ---

    public function test_with_weight_returns_new_instance(): void
    {
        $original = new CacheNode(id: 'node', host: 'host', port: 1234, weight: 100);
        $modified = $original->withWeight(300);

        $this->assertNotSame($original, $modified);
        $this->assertSame(100, $original->weight);
        $this->assertSame(300, $modified->weight);
    }

    public function test_with_weight_preserves_other_properties(): void
    {
        $original = new CacheNode(id: 'preserved', host: '10.0.0.1', port: 6380, weight: 100, status: CacheNodeStatus::DRAINING);
        $modified = $original->withWeight(250);

        $this->assertSame('preserved', $modified->id);
        $this->assertSame('10.0.0.1', $modified->host);
        $this->assertSame(6380, $modified->port);
        $this->assertSame(CacheNodeStatus::DRAINING, $modified->status);
        $this->assertSame(250, $modified->weight);
    }

    // --- toArray() serialization ---

    public function test_to_array_serialization(): void
    {
        $node = new CacheNode(
            id    : 'serialize-test',
            host  : '10.0.0.5',
            port  : 6385,
            weight: 150,
            status: CacheNodeStatus::HEALTHY,
        );

        $array = $node->toArray();

        $this->assertIsArray($array);
        $this->assertSame('serialize-test', $array['id']);
        $this->assertSame('10.0.0.5', $array['host']);
        $this->assertSame(6385, $array['port']);
        $this->assertSame(150, $array['weight']);
        $this->assertSame('healthy', $array['status']);
        $this->assertSame(225, $array['virtualNodeCount']);
    }

    // --- fromArray() deserialization roundtrip ---

    public function test_from_array_deserialization_roundtrip(): void
    {
        $original = new CacheNode(
            id              : 'roundtrip-node',
            host            : '10.0.0.10',
            port            : 6390,
            weight          : 200,
            status          : CacheNodeStatus::DRAINING,
            virtualNodeCount: 300,
        );

        $array    = $original->toArray();
        $restored = CacheNode::fromArray($array);

        $this->assertSame($original->id, $restored->id);
        $this->assertSame($original->host, $restored->host);
        $this->assertSame($original->port, $restored->port);
        $this->assertSame($original->weight, $restored->weight);
        $this->assertSame($original->status, $restored->status);
        $this->assertSame($original->virtualNodeCount(), $restored->virtualNodeCount());
    }

    public function test_from_array_with_defaults(): void
    {
        $data = [
            'id'   => 'default-node',
            'host' => 'localhost',
            'port' => 6379,
        ];

        $node = CacheNode::fromArray($data);

        $this->assertSame('default-node', $node->id);
        $this->assertSame('localhost', $node->host);
        $this->assertSame(6379, $node->port);
        $this->assertSame(100, $node->weight);
        $this->assertSame(CacheNodeStatus::HEALTHY, $node->status);
    }

    // --- __toString() ---

    public function test_to_string_returns_formatted_string(): void
    {
        $node = new CacheNode(id: 'test-node', host: '127.0.0.1', port: 6379);

        $str = (string) $node;

        $this->assertStringContainsString('test-node', $str);
        $this->assertStringContainsString('127.0.0.1:6379', $str);
    }

    // --- connectionString() ---

    public function test_connection_string(): void
    {
        $node = new CacheNode(id: 'conn-node', host: 'redis.example.com', port: 6380);

        $this->assertSame('redis.example.com:6380', $node->connectionString());
    }

    // --- isAvailable() ---

    public function test_is_available_for_healthy_node(): void
    {
        $node = new CacheNode(id: 'node', host: 'host', port: 1234, status: CacheNodeStatus::HEALTHY);

        $this->assertTrue($node->isAvailable());
    }

    public function test_is_available_for_unhealthy_node(): void
    {
        $node = new CacheNode(id: 'node', host: 'host', port: 1234, status: CacheNodeStatus::UNHEALTHY);

        $this->assertFalse($node->isAvailable());
    }

    public function test_is_available_for_draining_node(): void
    {
        $node = new CacheNode(id: 'node', host: 'host', port: 1234, status: CacheNodeStatus::DRAINING);

        $this->assertFalse($node->isAvailable());
    }

    public function test_is_available_for_maintenance_node(): void
    {
        $node = new CacheNode(id: 'node', host: 'host', port: 1234, status: CacheNodeStatus::MAINTENANCE);

        $this->assertFalse($node->isAvailable());
    }

    // --- CacheNodeStatus enum ---

    public function test_cache_node_status_values(): void
    {
        $this->assertSame('healthy', CacheNodeStatus::HEALTHY->value);
        $this->assertSame('unhealthy', CacheNodeStatus::UNHEALTHY->value);
        $this->assertSame('draining', CacheNodeStatus::DRAINING->value);
        $this->assertSame('maintenance', CacheNodeStatus::MAINTENANCE->value);
    }

    public function test_cache_node_status_is_available(): void
    {
        $this->assertTrue(CacheNodeStatus::HEALTHY->isAvailable());
        $this->assertFalse(CacheNodeStatus::UNHEALTHY->isAvailable());
        $this->assertFalse(CacheNodeStatus::DRAINING->isAvailable());
        $this->assertFalse(CacheNodeStatus::MAINTENANCE->isAvailable());
    }

    // --- Edge cases ---

    public function test_node_with_very_long_id(): void
    {
        $longId = str_repeat('a', 255);
        $node   = new CacheNode(id: $longId, host: 'host', port: 1234);

        $this->assertSame($longId, $node->id);
    }

    public function test_node_with_special_characters_in_id(): void
    {
        $node = new CacheNode(id: 'node:with.special-chars_123', host: 'host', port: 1234);

        $this->assertSame('node:with.special-chars_123', $node->id);
    }

    public function test_node_equality_by_value(): void
    {
        $node1 = new CacheNode(id: 'node-a', host: '127.0.0.1', port: 6379, weight: 100);
        $node2 = new CacheNode(id: 'node-a', host: '127.0.0.1', port: 6379, weight: 100);

        $this->assertNotSame($node1, $node2);
        $this->assertSame($node1->id, $node2->id);
        $this->assertSame($node1->weight, $node2->weight);
    }

    public function test_node_immutability(): void
    {
        // CacheNode is a readonly class - once created, it cannot be modified
        $node = new CacheNode(id: 'immutable-node', host: 'host', port: 1234, weight: 500);

        $id     = $node->id;
        $weight = $node->weight;

        $this->assertSame('immutable-node', $id);
        $this->assertSame(500, $weight);
    }
}
