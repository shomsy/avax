<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\MessageBus\System\Capabilities\Envelope;

use Avax\Components\Operations\MessageBus\System\Capabilities\Envelope\MessageEnvelope;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class MessageEnvelopeTest extends TestCase
{
    #[Test]
    public function it_constructs_with_required_fields() : void
    {
        $envelope = new MessageEnvelope(
            messageId: 'msg-1',
            type     : 'test.event',
            body     : ['key' => 'value'],
        );

        self::assertSame('msg-1', $envelope->messageId);
        self::assertSame('test.event', $envelope->type);
        self::assertSame(['key' => 'value'], $envelope->body);
        self::assertSame('', $envelope->correlationId);
        self::assertSame('', $envelope->causationId);
        self::assertSame(1, $envelope->version);
        self::assertSame(0, $envelope->timestamp);
        self::assertSame('', $envelope->source);
    }

    #[Test]
    public function it_constructs_with_all_fields() : void
    {
        $envelope = new MessageEnvelope(
            messageId    : 'msg-1',
            type         : 'test.event',
            body         : ['data' => true],
            correlationId: 'corr-1',
            causationId  : 'cause-1',
            version      : 3,
            timestamp    : 1234567890,
            source       : 'test-service',
        );

        self::assertSame('msg-1', $envelope->messageId);
        self::assertSame('test.event', $envelope->type);
        self::assertSame(['data' => true], $envelope->body);
        self::assertSame('corr-1', $envelope->correlationId);
        self::assertSame('cause-1', $envelope->causationId);
        self::assertSame(3, $envelope->version);
        self::assertSame(1234567890, $envelope->timestamp);
        self::assertSame('test-service', $envelope->source);
    }

    #[Test]
    public function it_is_readonly() : void
    {
        $envelope = new MessageEnvelope(
            messageId: 'msg-1',
            type     : 'test.event',
            body     : [],
        );

        $reflection = new ReflectionClass($envelope);
        self::assertTrue($reflection->isReadonly());
    }

    #[Test]
    public function it_creates_envelope_with_factory_method() : void
    {
        $envelope = MessageEnvelope::create(
            type: 'user.created',
            body: ['name' => 'John'],
        );

        self::assertStringStartsWith('msg_', $envelope->messageId);
        self::assertSame('user.created', $envelope->type);
        self::assertSame(['name' => 'John'], $envelope->body);
        self::assertStringStartsWith('corr_', $envelope->correlationId);
        self::assertSame('', $envelope->causationId);
        self::assertSame(1, $envelope->version);
        self::assertGreaterThan(0, $envelope->timestamp);
        self::assertSame('', $envelope->source);
    }

    #[Test]
    public function it_creates_envelope_with_explicit_correlation_id() : void
    {
        $envelope = MessageEnvelope::create(
            type         : 'order.placed',
            body         : ['order_id' => 1],
            correlationId: 'explicit-corr-id',
        );

        self::assertSame('explicit-corr-id', $envelope->correlationId);
    }

    #[Test]
    public function it_generates_unique_message_ids() : void
    {
        $envelope1 = MessageEnvelope::create(type: 'a', body: []);
        $envelope2 = MessageEnvelope::create(type: 'b', body: []);

        self::assertNotSame($envelope1->messageId, $envelope2->messageId);
    }

    #[Test]
    public function it_generates_unique_correlation_ids() : void
    {
        $envelope1 = MessageEnvelope::create(type: 'a', body: []);
        $envelope2 = MessageEnvelope::create(type: 'b', body: []);

        self::assertNotSame($envelope1->correlationId, $envelope2->correlationId);
    }

    #[Test]
    public function it_creates_from_array_with_all_fields() : void
    {
        $data = [
            'messageId'     => 'msg-from-array',
            'type'          => 'imported.event',
            'body'          => ['imported' => true],
            'correlationId' => 'corr-import',
            'causationId'   => 'cause-import',
            'version'       => 5,
            'timestamp'     => 9999999999,
            'source'        => 'importer',
        ];

        $envelope = MessageEnvelope::fromArray($data);

        self::assertSame('msg-from-array', $envelope->messageId);
        self::assertSame('imported.event', $envelope->type);
        self::assertSame(['imported' => true], $envelope->body);
        self::assertSame('corr-import', $envelope->correlationId);
        self::assertSame('cause-import', $envelope->causationId);
        self::assertSame(5, $envelope->version);
        self::assertSame(9999999999, $envelope->timestamp);
        self::assertSame('importer', $envelope->source);
    }

    #[Test]
    public function it_creates_from_array_with_missing_fields() : void
    {
        $data = [
            'type' => 'partial.event',
        ];

        $envelope = MessageEnvelope::fromArray($data);

        self::assertStringStartsWith('msg_', $envelope->messageId);
        self::assertSame('partial.event', $envelope->type);
        self::assertSame([], $envelope->body);
        self::assertSame('', $envelope->correlationId);
        self::assertSame('', $envelope->causationId);
        self::assertSame(1, $envelope->version);
        self::assertGreaterThan(0, $envelope->timestamp);
        self::assertSame('', $envelope->source);
    }

    #[Test]
    public function it_creates_from_empty_array() : void
    {
        $envelope = MessageEnvelope::fromArray([]);

        self::assertStringStartsWith('msg_', $envelope->messageId);
        self::assertSame('unknown', $envelope->type);
        self::assertSame([], $envelope->body);
        self::assertSame('', $envelope->correlationId);
        self::assertSame(1, $envelope->version);
    }

    #[Test]
    public function it_converts_to_array() : void
    {
        $envelope = new MessageEnvelope(
            messageId    : 'msg-1',
            type         : 'test.event',
            body         : ['x' => 1],
            correlationId: 'c-1',
            causationId  : 'ca-1',
            version      : 2,
            timestamp    : 100,
            source       : 'src',
        );

        $array = $envelope->toArray();

        self::assertSame([
                             'messageId' => 'msg-1',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 'type' => 'test.event',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 'body' => ['x' => 1],
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 'correlationId' => 'c-1',
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            'causationId' => 'ca-1',
                             'version'   => 2,
                             'timestamp' => 100,
                             'source'    => 'src',
                         ], $array);
    }

    #[Test]
    public function it_round_trips_through_array_conversion() : void
    {
        $original = new MessageEnvelope(
            messageId    : 'msg-rt',
            type         : 'round.trip',
            body         : ['data' => [1, 2, 3]],
            correlationId: 'corr-rt',
            causationId  : 'cause-rt',
            version      : 7,
            timestamp    : 500,
            source       : 'roundtripper',
        );

        $restored = MessageEnvelope::fromArray($original->toArray());

        self::assertSame($original->messageId, $restored->messageId);
        self::assertSame($original->type, $restored->type);
        self::assertSame($original->body, $restored->body);
        self::assertSame($original->correlationId, $restored->correlationId);
        self::assertSame($original->causationId, $restored->causationId);
        self::assertSame($original->version, $restored->version);
        self::assertSame($original->timestamp, $restored->timestamp);
        self::assertSame($original->source, $restored->source);
    }

    #[Test]
    public function it_creates_new_instance_with_causation() : void
    {
        $original = MessageEnvelope::create(type: 'event', body: []);

        $modified = $original->withCausation('new-cause');

        self::assertNotSame($original, $modified);
        self::assertSame($original->messageId, $modified->messageId);
        self::assertSame($original->type, $modified->type);
        self::assertSame($original->body, $modified->body);
        self::assertSame($original->correlationId, $modified->correlationId);
        self::assertSame('new-cause', $modified->causationId);
        self::assertSame($original->version, $modified->version);
        self::assertSame($original->timestamp, $modified->timestamp);
        self::assertSame($original->source, $modified->source);
    }

    #[Test]
    public function it_preserves_immutability_on_with_causation() : void
    {
        $original = new MessageEnvelope(
            messageId  : 'msg-1',
            type       : 'test',
            body       : [],
            causationId: 'old-cause',
        );

        $modified = $original->withCausation('new-cause');

        self::assertSame('old-cause', $original->causationId);
        self::assertSame('new-cause', $modified->causationId);
    }

    #[Test]
    public function it_handles_complex_body_payload() : void
    {
        $complexBody = [
            'user'     => [
                'id'    => 1,
                'name'  => 'Alice',
                'roles' => ['admin', 'editor'],
            ],
            'metadata' => [
                'ip'        => '127.0.0.1',
                'timestamp' => '2024-01-01T00:00:00Z',
            ],
        ];

        $envelope = new MessageEnvelope(
            messageId: 'msg-complex',
            type     : 'user.action',
            body     : $complexBody,
        );

        self::assertSame($complexBody, $envelope->body);
    }

    #[Test]
    public function it_handles_empty_body() : void
    {
        $envelope = MessageEnvelope::create(type: 'empty.event', body: []);

        self::assertSame([], $envelope->body);
    }
}
