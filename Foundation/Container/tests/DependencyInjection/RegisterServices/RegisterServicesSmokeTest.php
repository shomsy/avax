<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/bootstrap.php';

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

$container = makeTestContainer();
$container->bind(RegisterLoggerContract::class, DefaultRegisterLogger::class);
$container->when(NeedsSpecialLogger::class)->needs(RegisterLoggerContract::class)->give(SpecialRegisterLogger::class);

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

$default = $container->get(NeedsDefaultLogger::class);
$special = $container->get(NeedsSpecialLogger::class);
$message = $container->get(ExtensibleMessage::class);

assertSame('default', $default->logger->channel(), 'Default registration should remain default.');
assertSame('special', $special->logger->channel(), 'Target-specific override should win for the matching consumer.');
assertSame('configured', $message->name, 'Registration arguments should reach closure bindings.');
assertSame('extended-again', $message->value, 'Null-return extenders must keep the existing instance.');

echo basename(__FILE__) . " ok\n";
