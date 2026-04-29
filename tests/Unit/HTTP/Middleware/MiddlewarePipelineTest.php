<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\HTTP\Middleware;

use Avax\Components\HTTP\Middleware\System\Capabilities\Pipeline\MiddlewareStack;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for the Middleware Pipeline chain execution.
 */
final class MiddlewarePipelineTest extends TestCase
{
    #[Test]
    public function middleware_stack_pushes_and_retrieves_middleware() : void
    {
        $stack = new MiddlewareStack();

        $middleware1 = static function ($request, $next) {
            return $next($request);
        };

        $middleware2 = static function ($request, $next) {
            return $next($request);
        };

        $stack->push($middleware1);
        $stack->push($middleware2);

        $all = $stack->all();
        $this->assertCount(2, $all);
        $this->assertContains($middleware1, $all);
        $this->assertContains($middleware2, $all);
    }

    #[Test]
    public function middleware_stack_returns_empty_array_initially() : void
    {
        $stack = new MiddlewareStack();
        $this->assertEmpty($stack->all());
    }

    #[Test]
    public function middleware_chain_executes_in_order() : void
    {
        $executionOrder = [];

        $middleware1 = static function ($request, $next) use (&$executionOrder) {
            $executionOrder[] = 'before-1';
            $response         = $next($request);
            $executionOrder[] = 'after-1';

            return $response;
        };

        $middleware2 = static function ($request, $next) use (&$executionOrder) {
            $executionOrder[] = 'before-2';
            $response         = $next($request);
            $executionOrder[] = 'after-2';

            return $response;
        };

        $stack = new MiddlewareStack();
        $stack->push($middleware1);
        $stack->push($middleware2);

        // Simulate pipeline execution
        $coreHandler = static function ($request) use (&$executionOrder) {
            $executionOrder[] = 'core';

            return 'response';
        };

        $middlewares = $stack->all();
        $pipeline    = $coreHandler;

        // Build pipeline from inside out
        foreach (array_reverse($middlewares) as $middleware) {
            $next     = $pipeline;
            $pipeline = static function ($request) use ($middleware, $next) {
                return $middleware($request, $next);
            };
        }

        $pipeline('request');

        $this->assertEquals(
            expected: ['before-1', 'before-2', 'core', 'after-2', 'after-1'],
            actual  : $executionOrder,
        );
    }

    #[Test]
    public function middleware_can_short_circuit_pipeline() : void
    {
        $executionOrder = [];

        $blockingMiddleware = static function ($request, $next) use (&$executionOrder) {
            $executionOrder[] = 'blocking';

            // Don't call $next - short circuit
            return 'blocked';
        };

        $normalMiddleware = static function ($request, $next) use (&$executionOrder) {
            $executionOrder[] = 'normal';

            return $next($request);
        };

        $stack = new MiddlewareStack();
        $stack->push($blockingMiddleware);
        $stack->push($normalMiddleware);

        $coreHandler = static function ($request) use (&$executionOrder) {
            $executionOrder[] = 'core';

            return 'response';
        };

        $middlewares = $stack->all();
        $pipeline    = $coreHandler;

        foreach (array_reverse($middlewares) as $middleware) {
            $next     = $pipeline;
            $pipeline = static function ($request) use ($middleware, $next) {
                return $middleware($request, $next);
            };
        }

        $result = $pipeline('request');

        $this->assertEquals('blocked', $result);
        $this->assertEquals(['blocking'], $executionOrder);
    }

    #[Test]
    public function middleware_can_modify_request() : void
    {
        $modifiedRequest = null;

        $modifierMiddleware = static function ($request, $next) {
            $request['modified'] = true;

            return $next($request);
        };

        $stack = new MiddlewareStack();
        $stack->push($modifierMiddleware);

        $coreHandler = static function ($request) use (&$modifiedRequest) {
            $modifiedRequest = $request;

            return 'response';
        };

        $middlewares = $stack->all();
        $pipeline    = $coreHandler;

        foreach (array_reverse($middlewares) as $middleware) {
            $next     = $pipeline;
            $pipeline = static function ($request) use ($middleware, $next) {
                return $middleware($request, $next);
            };
        }

        $pipeline(['original' => true]);

        $this->assertArrayHasKey(key: 'modified', array: $modifiedRequest);
    }

    #[Test]
    public function middleware_can_modify_response() : void
    {
        $headerMiddleware = static function ($request, $next) {
            $response              = $next($request);
            $response['headers'][] = 'X-Custom-Header';

            return $response;
        };

        $stack = new MiddlewareStack();
        $stack->push($headerMiddleware);

        $coreHandler = static function ($request) {
            return ['body' => 'Hello', 'headers' => []];
        };

        $middlewares = $stack->all();
        $pipeline    = $coreHandler;

        foreach (array_reverse($middlewares) as $middleware) {
            $next     = $pipeline;
            $pipeline = static function ($request) use ($middleware, $next) {
                return $middleware($request, $next);
            };
        }

        $response = $pipeline(['method' => 'GET']);

        $this->assertContains('X-Custom-Header', $response['headers']);
    }

    #[Test]
    public function empty_stack_executes_core_handler_directly() : void
    {
        $stack = new MiddlewareStack();

        $coreHandler = static function ($request) {
            return "handled: {$request}";
        };

        $middlewares = $stack->all();
        $pipeline    = $coreHandler;

        foreach (array_reverse($middlewares) as $middleware) {
            $next     = $pipeline;
            $pipeline = static function ($request) use ($middleware, $next) {
                return $middleware($request, $next);
            };
        }

        $result = $pipeline('test');

        $this->assertEquals('handled: test', $result);
    }

    #[Test]
    public function multiple_middleware_layers_stack_correctly() : void
    {
        $stack = new MiddlewareStack();

        // Add 5 middleware
        for ($i = 1; $i <= 5; $i++) {
            $stack->push(static function ($request, $next) use ($i) {
                $request['layer'][]  = "enter-{$i}";
                $response            = $next($request);
                $response['layer'][] = "exit-{$i}";

                return $response;
            });
        }

        $coreHandler = static function ($request) {
            $request['layer'][] = 'core';

            return $request;
        };

        $middlewares = $stack->all();
        $pipeline    = $coreHandler;

        foreach (array_reverse($middlewares) as $middleware) {
            $next     = $pipeline;
            $pipeline = static function ($request) use ($middleware, $next) {
                return $middleware($request, $next);
            };
        }

        $result = $pipeline(['start' => true]);

        $expected = ['enter-1', 'enter-2', 'enter-3', 'enter-4', 'enter-5', 'core', 'exit-5', 'exit-4', 'exit-3', 'exit-2', 'exit-1'];
        $this->assertEquals($expected, $result['layer']);
    }
}
