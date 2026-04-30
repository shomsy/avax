<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Enterprise;

use Avax\Components\Operations\Mail\System\Capabilities\Address\Envelope;
use Avax\Components\Operations\Mail\System\Capabilities\Content\MimeMessage;
use Avax\Components\Operations\Mail\System\Capabilities\Queue\Mail;
use Avax\Components\Operations\Mail\System\Capabilities\Queue\Mailable;
use Avax\Components\Operations\Mail\System\Capabilities\Queue\MailQueue;
use Avax\Components\Operations\Mail\System\Capabilities\Queue\SmtpMailer;
use Avax\Components\Operations\Mail\System\Capabilities\Transport\LogTransport;
use Avax\Components\Operations\Mail\System\Flows\Send\SendMail;
use Avax\Components\Operations\Mail\System\PublicSurface\Mailer;
use Avax\Components\Tasks\System\Capabilities\Queue\Queue;
use Avax\Tests\TestCase;

final class MailQueueUnitTest extends TestCase
{
    public function test_mailable_builder_sets_recipient_subject_and_body() : void
    {
        $mailable = Mail::to(to: 'test@example.com')->subject(subject: 'Subject')->body(body: 'Body');

        self::assertSame(expected: 'test@example.com', actual: $mailable->getTo());
        self::assertSame(expected: 'Subject', actual: $mailable->getSubject());
        self::assertSame(expected: 'Body', actual: $mailable->getBody());
    }

    public function test_mailable_tracks_from_address() : void
    {
        $mailable = (new Mailable())->from(address: 'noreply@example.com', name: 'Avax');

        self::assertSame(expected: 'Avax <noreply@example.com>', actual: $mailable->getFrom());
    }

    public function test_mailable_tracks_cc_recipients() : void
    {
        $mailable = (new Mailable())->cc(address: 'cc@example.com');

        self::assertSame(expected: ['cc@example.com'], actual: $mailable->getCc());
    }

    public function test_mailable_tracks_attachments() : void
    {
        $mailable = (new Mailable())->attach(path: '/tmp/report.txt');

        self::assertSame(expected: 'report.txt', actual: $mailable->getAttachments()[0]['name']);
    }

    public function test_mail_queue_pushes_mail_jobs() : void
    {
        MailQueue::send(mailable: (new Mailable())->to(address: 'a@example.com'));

        self::assertSame(expected: 1, actual: MailQueue::queuedCount());
    }

    public function test_mail_queue_schedules_delayed_mail_jobs() : void
    {
        MailQueue::later(delay: 60, mailable: (new Mailable())->to(address: 'a@example.com'));

        self::assertSame(expected: 1, actual: Queue::size(queue: 'mail'));
    }

    public function test_smtp_mailer_array_driver_succeeds_without_network() : void
    {
        $mailer = new SmtpMailer(config: ['driver' => 'array']);

        self::assertTrue(condition: $mailer->send(mailable: (new Mailable())->to(address: 'a@example.com')));
    }

    public function test_mime_message_renders_raw_headers() : void
    {
        $message = new MimeMessage(from: 'a@example.com', to: 'b@example.com', subject: 'Hi', body: 'Body');

        self::assertStringContainsString(needle: 'Subject: Hi', haystack: $message->toRaw());
    }

    public function test_log_transport_records_successful_send() : void
    {
        $transport = new LogTransport();
        $result    = $transport->send(
            message : new MimeMessage(from: 'a@example.com', to: 'b@example.com', subject: 'Hi', body: 'Body'),
            envelope: new Envelope(from: 'a@example.com'),
        );

        self::assertTrue(condition: $result->success);
    }

    public function test_public_mailer_raw_builder_sends_through_flow() : void
    {
        $mailer = new Mailer(
            sendMail: new SendMail(transport: new LogTransport()),
            envelope: new Envelope(from: 'noreply@example.com'),
        );

        $result = $mailer->raw()
            ->from(address: 'noreply@example.com')
            ->to(address: 'user@example.com')
            ->subject(subject: 'Welcome')
            ->body(body: 'Hello')
            ->send();

        self::assertTrue(condition: $result->success);
    }

    protected function setUp() : void
    {
        parent::setUp();
        Queue::clear(queue: 'mail');
    }
}
