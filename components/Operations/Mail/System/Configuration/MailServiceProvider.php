<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Configuration;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Operations\Mail\System\Capabilities\Address\Envelope;
use Avax\Components\Operations\Mail\System\Capabilities\Transport\LogTransport;
use Avax\Components\Operations\Mail\System\Capabilities\Transport\MailTransport;
use Avax\Components\Operations\Mail\System\Flows\Send\SendMail;
use Avax\Components\Operations\Mail\System\PublicSurface\Mailer;

/**
 * MailServiceProvider — registers mail component dependencies.
 */
final class MailServiceProvider implements ServiceProvider
{
    public function register(ContainerInterface $container) : void
    {
        // Mail transport — default log transport
        $container->singleton(LogTransport::class, static fn () : LogTransport => new LogTransport());
        $container->singleton(MailTransport::class, static fn (ContainerInterface $c) : MailTransport => $c->get(LogTransport::class));

        // Default envelope
        $container->singleton(Envelope::class, static fn () : Envelope => new Envelope(from: 'noreply@localhost'));

        // Send mail flow
        $container->singleton(SendMail::class, static fn (ContainerInterface $c) : SendMail => new SendMail(
            mailTransport: $c->get(MailTransport::class),
        ));

        // Mailer public surface
        $container->singleton(Mailer::class, static fn (ContainerInterface $c) : Mailer => new Mailer(
            sendMail : $c->get(SendMail::class),
            envelope : $c->get(Envelope::class),
        ));
    }

    public function boot(ContainerInterface $container) : void
    {
        // No boot wiring needed
    }
}
