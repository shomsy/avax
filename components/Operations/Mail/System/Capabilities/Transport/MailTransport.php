<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Mail\System\Capabilities\Transport;

use Avax\Components\Operations\Mail\System\Capabilities\Address\Envelope;
use Avax\Components\Operations\Mail\System\Capabilities\Content\MimeMessage;

interface MailTransport
{
    public function send(MimeMessage $message, Envelope $envelope) : TransportResult;

    public function supports(string $driver) : bool;
}

final class TransportResult
{
    public function __construct(
        public readonly bool        $success,
        public readonly string|null $messageId = null,
        public readonly string|null $error = null,
    ) {}
}

final class SmtpTransport implements MailTransport
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send(MimeMessage $message, Envelope $envelope) : TransportResult
    {
        $host     = $this->config['host'] ?? 'localhost';
        $port     = $this->config['port'] ?? 25;
        $username = $this->config['username'] ?? '';
        $password = $this->config['password'] ?? '';
        $encryption = $this->config['encryption'] ?? null;

        $socket = @fsockopen(
            $encryption === 'ssl' ? "ssl://$host" : $host,
            $port,
            $errno,
            $errstr,
            30,
        );

        if (! $socket) {
            return new TransportResult(
                success: false,
                error  : "Failed to connect to mail server: $errstr ($errno)",
            );
        }

        $response = fgets($socket, 515);
        if ((int) substr($response, 0, 3) !== 220) {
            fclose($socket);

            return new TransportResult(success: false, error: "SMTP connection failed: $response");
        }

        $this->sendCommand($socket, 'EHLO ' . gethostname());
        if (! empty($username)) {
            $this->sendCommand($socket, 'AUTH LOGIN');
            $this->sendCommand($socket, base64_encode($username));
            $this->sendCommand($socket, base64_encode($password));
        }

        $this->sendCommand($socket, "MAIL FROM:<{$envelope->from}>");
        $this->sendCommand($socket, "RCPT TO:<{$message->to}>");
        $this->sendCommand($socket, 'DATA');

        $rawMessage = $message->toRaw();
        fwrite($socket, $rawMessage . "\r\n.\r\n");
        $response = fgets($socket, 515);

        $this->sendCommand($socket, 'QUIT');
        fclose($socket);

        if ((int) substr($response, 0, 3) === 250) {
            return new TransportResult(
                success  : true,
                messageId: '<' . uniqid('msg-') . '@' . gethostname() . '>',
            );
        }

        return new TransportResult(success: false, error: "SMTP send failed: $response");
    }

    private function sendCommand($socket, string $command) : void
    {
        fwrite($socket, "$command\r\n");
        fgets($socket, 515);
    }

    public function supports(string $driver) : bool
    {
        return $driver === 'smtp';
    }
}

final class SendmailTransport implements MailTransport
{
    private string $command;

    public function __construct(string $command = '/usr/sbin/sendmail -bs')
    {
        $this->command = $command;
    }

    public function send(MimeMessage $message, Envelope $envelope) : TransportResult
    {
        $descriptorSpec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($this->command, $descriptorSpec, $pipes);

        if (! is_resource($process)) {
            return new TransportResult(success: false, error: 'Failed to start sendmail');
        }

        fwrite($pipes[0], $message->toRaw());
        fclose($pipes[0]);

        $output = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode === 0) {
            return new TransportResult(
                success  : true,
                messageId: '<' . uniqid('msg-') . '@' . gethostname() . '>',
            );
        }

        return new TransportResult(success: false, error: "Sendmail failed: $output");
    }

    public function supports(string $driver) : bool
    {
        return $driver === 'sendmail';
    }
}

final class LogTransport implements MailTransport
{
    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function send(MimeMessage $message, Envelope $envelope) : TransportResult
    {
        $this->logger->info('Mail sent', [
            'from'    => $message->from,
            'to'      => $message->to,
            'subject' => $message->subject,
        ]);

        return new TransportResult(
            success  : true,
            messageId: '<' . uniqid('msg-') . '-logged@local>',
        );
    }

    public function supports(string $driver) : bool
    {
        return $driver === 'log';
    }
}

final class NullTransport implements MailTransport
{
    public function send(MimeMessage $message, Envelope $envelope) : TransportResult
    {
        return new TransportResult(
            success  : true,
            messageId: '<' . uniqid('msg-') . '-null@local>',
        );
    }

    public function supports(string $driver) : bool
    {
        return $driver === 'null';
    }
}
