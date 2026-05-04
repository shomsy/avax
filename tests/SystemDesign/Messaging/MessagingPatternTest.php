<?php

declare(strict_types=1);

namespace Avax\Components\mDesign\Messaging;

use Avax\Components\SystemDesign\Messaging\System\Flows\AnalyzeMessaging\AnalyzeMessaging;
use Avax\Components\SystemDesign\Messaging\System\PublicSurface\MessagingPattern;
use Avax\Tests\TestCase;

final class MessagingPatternTest extends TestCase
{
    public function test_analyzes_request_reply_pattern(): void
    {
        $flow = new AnalyzeMessaging();
        $result = $flow(MessagingPattern::REQUEST_REPLY);

        $this->assertSame('request-reply', $result['pattern']);
        $this->assertSame('required', $result['ordering']);
        $this->assertSame('optional', $result['idempotency']);
    }

    public function test_analyzes_sagas_pattern(): void
    {
        $flow = new AnalyzeMessaging();
        $result = $flow(MessagingPattern::SAGAS);

        $this->assertSame('sagas', $result['pattern']);
        $this->assertSame('critical', $result['idempotency']);
        $this->assertSame('saga-rollback', $result['compensation']);
    }

    public function test_all_messaging_patterns_exist(): void
    {
        $this->assertCount(5, MessagingPattern::cases());
    }
}
