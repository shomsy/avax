<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Messaging\System\Flows\AnalyzeMessaging;

use Avax\Components\SystemDesign\Messaging\System\PublicSurface\MessagingPattern;

final readonly class AnalyzeMessaging
{
    /**
     * @return array<string, mixed>
     */
    public function __invoke(MessagingPattern $pattern): array
    {
        return [
            'pattern' => $pattern->value,
            'ordering' => match ($pattern) {
                MessagingPattern::FIRE_AND_FORGET => 'none',
                MessagingPattern::REQUEST_REPLY => 'required',
                MessagingPattern::PIPELINE => 'required',
                MessagingPattern::PUBLISH_SUBSCRIBE => 'optional',
                MessagingPattern::SAGAS => 'required',
            },
            'idempotency' => match ($pattern) {
                MessagingPattern::FIRE_AND_FORGET => 'required',
                MessagingPattern::REQUEST_REPLY => 'optional',
                MessagingPattern::PIPELINE => 'required',
                MessagingPattern::PUBLISH_SUBSCRIBE => 'required',
                MessagingPattern::SAGAS => 'critical',
            },
            'compensation' => match ($pattern) {
                MessagingPattern::FIRE_AND_FORGET => 'none',
                MessagingPattern::REQUEST_REPLY => 'none',
                MessagingPattern::PIPELINE => 'retry',
                MessagingPattern::PUBLISH_SUBSCRIBE => 'none',
                MessagingPattern::SAGAS => 'saga-rollback',
            },
        ];
    }
}
