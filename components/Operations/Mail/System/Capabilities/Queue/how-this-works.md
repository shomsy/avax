---
title: Queue-how-this-works
owner: operations-mail
last_reviewed: 2026-04-30
classification: internal
---

# Queue How This Works

## What this folder is

This folder owns queued mail construction and dispatch. `Mailable` holds the user-facing message, `MailQueue` pushes
work into the task queue, and `SmtpMailer` sends immediate mail.

## Real commands or triggers that reach this folder

Application code calls `Mail::to()`, `Mail::send()`, `MailQueue::send()`, or `MailQueue::later()`.

## Exact upstream handoffs

`Mail` is the public builder facade. `MailQueue` delegates background work to `Tasks\System\Capabilities\Queue\Queue`.

## Failure behavior

The array driver succeeds without network access for tests. Real SMTP sending uses PHP mail and returns `false` if the
local transport rejects the message.
