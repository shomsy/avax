# Providers/HTTP

## What This Folder Represents

This folder contains providers that wire HTTP-related services into the container.

Technically, these providers register HTTP clients, router and middleware services, request/session scope helpers, and
view rendering integrations. They often define scope boundaries (per-request) and register the services needed to build
an HTTP pipeline.

### For Humans: What This Means (Represent)

It’s the “teach the container how to run a web request” folder.

## What Belongs Here

- Providers for routing, middleware, sessions, HTTP clients, and views.

### For Humans: What This Means (Belongs)

If it helps you handle HTTP requests using DI, it lives here.

## What Does NOT Belong Here

- Your application controllers and routes (those belong in your app).
- Core kernel internals (those belong in `Core/Kernel`).

### For Humans: What This Means (Not Belongs)

Providers give you the tools, but your app still decides what to build.

## How Files Collaborate

HTTP providers register the services needed for request handling. The container then resolves those services per
request, often using scoped lifetimes so request-specific state doesn’t leak.

### For Humans: What This Means (Collaboration)

Providers wire the web stack once so your runtime request handling stays clean.

