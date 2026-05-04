<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\Messaging\System\PublicSurface;

enum MessagingPattern: string
{
    case FIRE_AND_FORGET = 'fire-and-forget';
    case REQUEST_REPLY = 'request-reply';
    case PIPELINE = 'pipeline';
    case PUBLISH_SUBSCRIBE = 'publish-subscribe';
    case SAGAS = 'sagas';
}
