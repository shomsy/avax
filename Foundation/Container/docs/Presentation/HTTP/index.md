# Presentation/HTTP

## What This Folder Represents and Why It Exists

Technical: Contains HTTP-facing assets such as route definitions and HTTP-specific presentation glue that sit on top of
the routing stack.

### For Humans: What This Means

This is the HTTP slice of the front door—requests come in here and get mapped to controllers.

## What Belongs Here

Technical: HTTP route files, HTTP adapters, and any presentation helpers strictly tied to web delivery.

### For Humans: What This Means

Put web routes and HTTP-only helpers here.

## What Does NOT Belong Here

Technical: Core container code, CLI tooling, domain services, or database logic.

### For Humans: What This Means

Keep non-HTTP code out; this folder is only for things the web layer needs.

## How Files Collaborate

Technical: Route files feed routes to the container-managed router; controllers resolved by the router use services
bound by providers.

### For Humans: What This Means

Routes defined here tell the router where to send requests; controllers then use the services the container already
knows about.
