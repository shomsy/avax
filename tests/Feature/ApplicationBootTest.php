<?php

namespace Tests\Feature;

use Avax\Container\Features\Operate\Boot\Application;
use Avax\HTTP\Router\Router;
use PHPUnit\Framework\TestCase;

class ApplicationBootTest extends TestCase
{
    /**
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Avax\Container\Features\Core\Exceptions\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function test_application_boots_and_serves_default_route(): void
    {
        /** @var Application $app */
        $app = require __DIR__ . '/../../bootstrap/bootstrap.php';

        $app->boot();

        // Manually create a request since we are in CLI
        // Assuming Request::create exists or similar constructor
        // If Request::create doesn't exist, we might need to mock globals or use constructor if public
        // checking Request class previously...

        // Let's assume standard instantiation if create helper missing, but usually they have one.
        // I'll try to use a mock or simpler approach if unsure, but let's try assuming standard request factory behavior or just check Response.

        // Actually, let's rely on the container to give us the router
        $router = $app->getContainer()->get(Router::class);

        // We need to construct a Request object. 
        // I don't have the Request class definition handy to know its factory methods, 
        // but Application uses Request::createFromGlobals().
        // I will assume I can instantiate it or there is a static create/make.
        // Let's check Request class file if I can find it, to be sure.

        // For now, I'll assume I can set $_SERVER and use createFromGlobals() if needed, 
        // OR just try to run $app->run() with output buffering.

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI']    = '/';
        $_SERVER['HTTP_HOST']      = 'localhost';

        ob_start();
        $response = $app->run();
        $output   = ob_get_clean();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString('Avax components router is up.', (string) $response->getBody());
    }
}
