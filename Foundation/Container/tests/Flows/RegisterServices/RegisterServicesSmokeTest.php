<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

use Avax\Container\DI\Capabilities\Declaration\Bindings\DecoratorInterface;
use Avax\Container\DI\ContainerInterface;

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
    public RegisterLoggerContract $logger;

    public function __construct(RegisterLoggerContract $logger) { $this->logger = $logger; }
}

final class NeedsSpecialLogger
{
    public RegisterLoggerContract $logger;

    public function __construct(RegisterLoggerContract $logger) { $this->logger = $logger; }
}

final class ExtensibleMessage
{
    public string $value = 'base';
    public string $name  = 'unset';

    public function __construct(string $name = 'unset') { $this->name = $name; }
}

final class MessageDecorator implements DecoratorInterface
{
    public function decorate(mixed $instance, ContainerInterface|null $container = null) : mixed
    {
        assertInstanceOf(expectedClass: ExtensibleMessage::class, value: $instance, message: 'Decorator contract should receive the resolved service instance.');
        $message        = $instance;
        $message->value = 'decorated:' . $message->value;

        return $message;
    }
}

$container = makeTestContainer();
$container->bind(abstract: RegisterLoggerContract::class, concrete: DefaultRegisterLogger::class);
$container->alias(alias: 'register.logger', abstract: RegisterLoggerContract::class);
$container->when(consumer: NeedsSpecialLogger::class)->needs(abstract: RegisterLoggerContract::class)->give(implementation: SpecialRegisterLogger::class);
$container->singleton(abstract: DefaultRegisterLogger::class, concrete: DefaultRegisterLogger::class);
$container->singleton(abstract: SpecialRegisterLogger::class, concrete: SpecialRegisterLogger::class);
$container->tag(abstracts: [DefaultRegisterLogger::class, SpecialRegisterLogger::class], tags: 'logger');

$container
    ->singleton(
        abstract: ExtensibleMessage::class,
        concrete: static fn ($app, array $arguments = []) => new ExtensibleMessage(name: $arguments['name'] ?? 'missing')
    )
    ->withArgument(name: 'name', value: 'configured');

$container->extend(
    abstract: ExtensibleMessage::class,
    closure : static function (ExtensibleMessage $message) : ExtensibleMessage {
        $message->value = 'extended';

        return $message;
    }
);
$container->extend(
    abstract: ExtensibleMessage::class,
    closure : static function (ExtensibleMessage $message) : null {
        $message->value = 'extended-again';

        return null;
    }
);
$container->decorate(abstract: ExtensibleMessage::class, decorator: new MessageDecorator());

$default            = $container->get(id: NeedsDefaultLogger::class);
$special            = $container->get(id: NeedsSpecialLogger::class);
$aliased            = $container->get(id: 'register.logger');
$message            = $container->get(id: ExtensibleMessage::class);
$tagged             = $container->tagged(tag: 'logger');
$messageDescription = $container->describeService(id: ExtensibleMessage::class);

assertSame(expected: 'default', actual: $default->logger->channel(), message: 'Default registration should remain default.');
assertSame(expected: 'special', actual: $special->logger->channel(), message: 'Target-specific override should win for the matching consumer.');
assertSame(expected: 'default', actual: $aliased->channel(), message: 'Aliases should resolve to their target service.');
assertSame(expected: 'configured', actual: $message->name, message: 'Registration arguments should reach closure bindings.');
assertSame(expected: 'decorated:extended-again', actual: $message->value, message: 'Decorators should wrap the resolved instance after extenders.');
assertSame(
    expected: ['extender', 'extender', MessageDecorator::class],
    actual  : $messageDescription['decorationChain'],
    message : 'Decoration diagnostics should preserve deterministic decoration ordering.'
);
assertSame(expected: 2, actual: count($tagged), message: 'Tagged services should resolve back into service instances.');
assertInstanceOf(expectedClass: DefaultRegisterLogger::class, value: $tagged[0], message: 'Tagged resolution should return the registered service.');

echo basename(__FILE__) . " ok\n";
