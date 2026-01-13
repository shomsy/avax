# Presentation

## What This Folder Represents and Why It Exists

Technical: Presentation hosts user-facing entrypoints (HTTP routes, controllers, views) that sit on top of the container
and service providers. It separates delivery concerns from core infrastructure.

### For Humans: What This Means

This is the “front door” layer—everything the outside world touches lives here, away from DI internals.

## What Belongs Here

Technical: Route definitions, controllers, view adapters, and other delivery-specific glue that depends on services
already registered in the container.

### For Humans: What This Means

Put the code that handles incoming requests and shapes outgoing responses here.

## What Does NOT Belong Here

Technical: Core container wiring, provider registration, low-level kernel logic, or shared domain models; those live
under `Foundation` and feature folders.

### For Humans: What This Means

Keep engine parts and domain rules elsewhere—this layer is only for how you talk to the outside world.

## How Files Collaborate

Technical: Route files invoke the router; controllers consume services from the container; views render data returned by
controllers.

### For Humans: What This Means

Routes direct traffic to controllers, controllers pull dependencies from the container, and views present the results.
