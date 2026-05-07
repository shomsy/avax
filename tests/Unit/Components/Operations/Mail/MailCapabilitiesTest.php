<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Operations\Mail;

use Avax\Components\Operations\Mail\System\Capabilities\Address\Envelope;
use Avax\Components\Operations\Mail\System\Capabilities\Content\MimeMessage;
use Avax\Components\Operations\Mail\System\Capabilities\Transport\LogTransport;
use Avax\Components\Operations\Mail\System\Capabilities\Transport\NullTransport;
use PHPUnit\Framework\TestCase;

final class MailCapabilitiesTest extends TestCase
{
    // --- NullTransport ---

    public function test_it_sends_via_null_transport_and_returns_success() : void
    {
        $transport = new NullTransport();
        $message   = new MimeMessage('from@test.com', 'to@test.com', 'Test', 'Body');
        $envelope  = new Envelope('from@test.com');
        $result    = $transport->send($message, $envelope);
        $this->assertTrue($result->success);
        $this->assertNotEmpty($result->messageId);
    }

    public function test_null_transport_supports_null_driver() : void
    {
        $transport = new NullTransport();
        $this->assertTrue($transport->supports('null'));
        $this->assertFalse($transport->supports('smtp'));
    }

    // --- LogTransport ---

    public function test_it_records_messages_in_log_transport() : void
    {
        $transport = new LogTransport();
        $message   = new MimeMessage('from@test.com', 'to@test.com', 'Hello', 'Body text');
        $envelope  = new Envelope('from@test.com');
        $transport->send($message, $envelope);

        $messages = $transport->messages();
        $this->assertCount(1, $messages);
        $this->assertSame('to@test.com', $messages[0]['to']);
        $this->assertSame('Hello', $messages[0]['subject']);
    }

    public function test_log_transport_supports_log_driver() : void
    {
        $transport = new LogTransport();
        $this->assertTrue($transport->supports('log'));
        $this->assertFalse($transport->supports('smtp'));
    }

    // --- Envelope ---

    public function test_it_creates_envelope_with_from_address() : void
    {
        $envelope = new Envelope('sender@test.com');
        $this->assertSame('sender@test.com', $envelope->from);
    }

    public function test_it_creates_new_envelope_with_different_from() : void
    {
        $envelope = new Envelope('old@test.com');
        $new      = $envelope->withFrom('new@test.com');
        $this->assertSame('new@test.com', $new->from);
        $this->assertSame('old@test.com', $envelope->from);
    }

    public function test_it_creates_new_envelope_with_return_path() : void
    {
        $envelope = new Envelope('sender@test.com');
        $new      = $envelope->withReturnPath('bounce@test.com');
        $this->assertSame('bounce@test.com', $new->returnPath);
    }

    // --- MimeMessage ---

    public function test_it_creates_plain_text_message() : void
    {
        $msg = new MimeMessage('from@x.com', 'to@x.com', 'Subject', 'Body');
        $this->assertSame('text/plain', $msg->contentType);
    }

    public function test_it_creates_html_variant() : void
    {
        $msg  = new MimeMessage('f@x.com', 't@x.com', 'S', 'B');
        $html = $msg->html('<h1>Hello</h1>');
        $this->assertSame('text/html', $html->contentType);
        $this->assertSame('<h1>Hello</h1>', $html->body);
    }

    public function test_it_adds_attachment_immutably() : void
    {
        $msg            = new MimeMessage('f@x.com', 't@x.com', 'S', 'B');
        $withAttachment = $msg->withAttachment('doc.pdf', 'content', 'application/pdf');
        $this->assertCount(0, $msg->attachments);
        $this->assertCount(1, $withAttachment->attachments);
        $this->assertSame('doc.pdf', $withAttachment->attachments[0]['filename']);
    }

    public function test_it_renders_raw_message() : void
    {
        $msg = new MimeMessage('f@x.com', 't@x.com', 'Test', 'Hello World');
        $raw = $msg->toRaw();
        $this->assertStringContainsString('From: f@x.com', $raw);
        $this->assertStringContainsString('To: t@x.com', $raw);
        $this->assertStringContainsString('Subject: Test', $raw);
        $this->assertStringContainsString('Hello World', $raw);
    }
}
