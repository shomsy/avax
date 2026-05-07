<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Presentation\System;

use Avax\Components\Presentation\System\Flows\Render\Render;
use Avax\Components\Presentation\System\Foundation\PresentationFailure;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PresentationSystemCapabilitiesTest extends TestCase
{
    public function test_it_renders_empty_string_by_default() : void
    {
        $result = Render::render('welcome');
        $this->assertSame('', $result);
    }

    public function test_presentation_failure_is_runtime_exception() : void
    {
        $failure = new PresentationFailure('Rendering failed');
        $this->assertInstanceOf(RuntimeException::class, $failure);
        $this->assertSame('Rendering failed', $failure->getMessage());
    }
}
