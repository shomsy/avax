<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/bootstrap.php';

use Avax\Container\ContainerInterface;
use Avax\Container\DependencyInjection\Dependencies\Bindings\DecoratorInterface;

interface RegisterLoggerContract
{
    public function channel() : string;
}

final class DefaultRegisterLogger implements RegisterLoggerContract
{
    public function channel() : string
    {
        return 'default';
    }
}

final class SpecialRegisterLogger implements RegisterLoggerContract
{
    public function channel() : string
    {
        return 'special';
    }
}

final class NeedsDefaultLogger
{
    public function __construct(public RegisterLoggerContract $logger)
    {
    }
}

final class NeedsSpecialLogger
{
    public function __construct(public RegisterLoggerContract $logger)
    {
    }
}

final class ExtensibleMessage
{
    public string $value = 'base';

    public function __construct(public string $name = 'unset')
    {
    }
}

final class MessageDecorator implements DecoratorInterface
{
    public function decorate(mixed $instance, ContainerInterface|null $container = null) : mixed
    {
        assertInstanceOf(ExtensibleMessage::class, $instance, 'Decorator contract should receive the resolved service instance.');
        $message = $instance;
        $message->value = 'decorated:' . $message->value;

        return $message;
    }
}

$container = makeTestContainer();
$container->bind(RegisterLoggerContract::class, DefaultRegisterLogger::class);
$container->alias('register.logger', RegisterLoggerContract::class);
$container->when(NeedsSpecialLogger::class)->needs(RegisterLoggerContract::class)->give(SpecialRegisterLogger::class);
$container->singleton(DefaultRegisterLogger::class, DefaultRegisterLogger::class);
$container->singleton(SpecialRegisterLogger::class, SpecialRegisterLogger::class);
$container->tag([DefaultRegisterLogger::class, SpecialRegisterLogger::class], 'logger');

$container
    ->singleton(
        ExtensibleMessage::class,
        static fn($app, array $arguments = []) => new ExtensibleMessage($arguments['name'] ?? 'missing')
    )
    ->withArgument('name', 'configured');

$container->extend(
    ExtensibleMessage::class,
    static function (ExtensibleMessage $message) : ExtensibleMessage {
        $message->value = 'extended';

        return $message;
    }
);
$container->extend(
    ExtensibleMessage::class,
    static function (ExtensibleMessage $message) : null {
        $message->value = 'extended-again';

        return null;
    }
);
$container->decorate(ExtensibleMessage::class, new MessageDecorator());

$default = $container->get(NeedsDefaultLogger::class);
$special = $container->get(NeedsSpecialLogger::class);
$aliased = $container->get('register.logger');
$message = $container->get(ExtensibleMessage::class);
$tagged = $container->tagged('logger');
$messageDescription = $container->describeService(ExtensibleMessage::class);

assertSame('default', $default->logger->channel(), 'Default registration should remain default.');
assertSame('special', $special->logger->channel(), 'Target-specific override should win for the matching consumer.');
assertSame('default', $aliased->channel(), 'Aliases should resolve to their target service.');
assertSame('configured', $message->name, 'Registration arguments should reach closure bindings.');
assertSame('decorated:extended-again', $message->value, 'Decorators should wrap the resolved instance after extenders.');
assertSame(
    ['extender', 'extender', MessageDecorator::class],
    $messageDescription['decorationChain'],
    'Decoration diagnostics should preserve deterministic decoration ordering.'
);
assertSame(2, count($tagged), 'Tagged services should resolve back into service instances.');
assertInstanceOf(DefaultRegisterLogger::class, $tagged[0], 'Tagged resolution should return the registered service.');

echo basename(__FILE__) . " ok\n";
