# Business Logic (Per-Project Only)

This folder is intentionally empty in the reusable `agent-harness` repo.

Each child project must define its own `business-logic/` content.

## What Belongs Here In a Child Repo

- product and domain meaning in plain language
- user goals and visible value model
- how software behavior supports those goals

## Required Questions

Every business note or domain rule should answer:

1. What outcome should the user or operator achieve?
2. What should the software do to make that outcome reliable?
3. What should be visible or materially different at the boundary?
4. What should the user or operator understand, trust, or be able to do afterwards?

## Rule

- Write user-facing meaning before implementation detail.
- Do not reuse `business-logic/` between projects; it is always local.
