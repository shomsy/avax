<?php

declare(strict_types=1);

namespace Avax\Tests\GoldenPathRuntime;

use Avax\Components\Operations\MessageBus\System\PublicSurface\Command;
use Avax\Components\Operations\MessageBus\System\PublicSurface\DomainEvent;
use Avax\Components\Operations\MessageBus\System\PublicSurface\MessageBus;
use Avax\Components\Operations\MessageBus\System\PublicSurface\Query;
use Avax\Components\Operations\Observability\System\Capabilities\Logging\Logger;
use Avax\Examples\GoldenPathRuntimeApp\Domain\Message\GetWebhookStatus;
use Avax\Examples\GoldenPathRuntimeApp\Domain\Message\IngestWebhook;
use Avax\Examples\GoldenPathRuntimeApp\Domain\Message\WebhookIngested;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Proves: MessageBus dispatches commands, queries, and events.
 */
final class MessageDispatchTest extends TestCase
{
    #[Test]
    public function commandDispatchReturnsResult() : void
    {
        $logger         = new Logger();
        $handlerInvoked = false;

        $handler = static function (IngestWebhook $command) use (&$handlerInvoked, $logger) : array {
            $handlerInvoked = true;
            $logger->info('Webhook ingested', ['webhook_id' => $command->webhookId]);

            return ['status' => 'accepted', 'webhook_id' => $command->webhookId];
        };

        MessageBus::listen(IngestWebhook::class, $handler);

        $command = new IngestWebhook(
            webhookId    : 'wh_001',
            source       : 'github',
            payload      : '{"event":"push"}',
            correlationId: 'corr_001',
        );

        $result = MessageBus::dispatch($command);

        self::assertTrue($handlerInvoked);
        self::assertIsArray($result);
        self::assertSame('accepted', $result['status']);
        self::assertSame('wh_001', $result['webhook_id']);

        // Verify logger recorded the entry
        self::assertCount(1, $logger->records());
    }

    #[Test]
    public function queryDispatchReturnsData() : void
    {
        $handlerInvoked = false;

        $handler = static function (GetWebhookStatus $query) use (&$handlerInvoked) : array {
            $handlerInvoked = true;

            return [
                'webhook_id' => $query->webhookId,
                'status'     => 'processed',
                'state'      => 'completed',
            ];
        };

        MessageBus::listen(GetWebhookStatus::class, $handler);

        $result = MessageBus::query(new GetWebhookStatus(webhookId: 'wh_002'));

        self::assertTrue($handlerInvoked);
        self::assertIsArray($result);
        self::assertSame('wh_002', $result['webhook_id']);
        self::assertSame('processed', $result['status']);
    }

    #[Test]
    public function eventPublishNotifiesAllListeners() : void
    {
        $notified = [];

        $handler1 = static function (WebhookIngested $event) use (&$notified) : void {
            $notified[] = 'handler1';
        };

        $handler2 = static function (WebhookIngested $event) use (&$notified) : void {
            $notified[] = 'handler2';
        };

        MessageBus::listen(WebhookIngested::class, $handler1);
        MessageBus::listen(WebhookIngested::class, $handler2);

        MessageBus::publish(new WebhookIngested(
                                webhookId: 'wh_003',
                                source   : 'github',
                                timestamp: date('c'),
                            ));

        self::assertSame(['handler1', 'handler2'], $notified);
    }

    #[Test]
    public function messageTypesAreCorrectlyClassified() : void
    {
        self::assertInstanceOf(Command::class, new IngestWebhook('id', 'src', '{}', 'corr'));
        self::assertInstanceOf(Query::class, new GetWebhookStatus('id'));
        self::assertInstanceOf(DomainEvent::class, new WebhookIngested('id', 'src', 'now'));
    }
}
