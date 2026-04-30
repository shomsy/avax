<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\HTTP\Response\Flows;

use Avax\Components\HTTP\Response\Flows\EmitResponse\EmitResponse;
use Avax\Components\HTTP\Response\Response;
use PHPUnit\Framework\TestCase;

final class EmitResponseTest extends TestCase
{
    public function test_emit_response_writes_status_headers_and_body() : void
    {
        http_response_code(response_code: 200);
        header_remove();

        ob_start();
        new EmitResponse()(response: Response::text(content: 'emitted body', status: 202));
        $output = (string) ob_get_clean();

        self::assertSame(202, http_response_code());
        self::assertSame('emitted body', $output);
    }
}
