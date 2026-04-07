<?php

declare(strict_types=1);

namespace {
    if (! function_exists('appInstance')) {
        function appInstance($instance = null)
        {
            static $container = null;

            if ($instance !== null) {
                $container = $instance;
            }

            if ($container === null) {
                throw new RuntimeException(
                    message: 'Container instance is not initialized. Please set the container first.'
                );
            }

            return $container;
        }
    }

    if (! function_exists('app')) {
        function app(string|null $abstract = null) : mixed
        {
            $container = appInstance();
            if ($abstract === null) {
                return $container;
            }

            return $container->get($abstract);
        }
    }
}

namespace Avax\Container\Tests\Capabilities\Providers\Runtime\Http {

    use Avax\HTTP\Request\Request;
    use Avax\HTTP\Session\NullSession;
    use Avax\HTTP\Session\Shared\Contracts\SessionInterface;
    use PHPUnit\Framework\TestCase;
    use ReflectionProperty;
    use RuntimeException;
    use SensitiveParameter;
    use stdClass;

    final readonly class FakeContainer
    {
        public function __construct(
            private bool $hasSession,
            #[SensitiveParameter] private mixed $session
        ) {}

        public function has(string $id) : bool
        {
            return $this->hasSession && $id === SessionInterface::class;
        }

        public function get(string $id) : mixed
        {
            if ($id === SessionInterface::class && $this->hasSession) {
                return $this->session;
            }

            throw new RuntimeException(message: "Service {$id} not found");
        }
    }

    final class RequestFromGlobalsTest extends TestCase
    {
        private array $serverBackup = [];

        private array $getBackup = [];

        private array $postBackup = [];

        private array $cookieBackup = [];

        private array $filesBackup = [];

        public function test_create_from_globals_ignores_invalid_session_binding() : void
        {
            appInstance(instance: new FakeContainer(hasSession: true, session: new stdClass));

            $request = Request::createFromGlobals();

            $this->assertInstanceOf(expected: Request::class, actual: $request);
            $this->assertInstanceOf(expected: NullSession::class, actual: $this->extractSession(request: $request));
        }

        public function test_create_from_globals_uses_session_interface() : void
        {
            $session = new NullSession;
            appInstance(instance: new FakeContainer(hasSession: true, session: $session));

            $request = Request::createFromGlobals();

            $this->assertSame(expected: $session, actual: $this->extractSession(request: $request));
        }

        protected function setUp() : void
        {
            parent::setUp();

            $this->serverBackup = $_SERVER ?? [];
            $this->getBackup    = $_GET ?? [];
            $this->postBackup   = $_POST ?? [];
            $this->cookieBackup = $_COOKIE ?? [];
            $this->filesBackup  = $_FILES ?? [];

            $_SERVER = [
                'HTTP_HOST'       => 'components.test',
                'REQUEST_URI'     => '/',
                'REQUEST_METHOD'  => 'GET',
                'SERVER_PROTOCOL' => '1.1',
                'SERVER_PORT'     => '443',
                'QUERY_STRING'    => '',
            ];
            $_GET    = [];
            $_POST   = [];
            $_COOKIE = [];
            $_FILES  = [];
        }

        protected function tearDown() : void
        {
            $_SERVER = $this->serverBackup;
            $_GET    = $this->getBackup;
            $_POST   = $this->postBackup;
            $_COOKIE = $this->cookieBackup;
            $_FILES  = $this->filesBackup;

            parent::tearDown();
        }

        private function extractSession(Request $request) : SessionInterface
        {
            $property = new ReflectionProperty(class: Request::class, property: 'session');
            $property->setAccessible(accessible: true);

            return $property->getValue(object: $request);
        }
    }
}
