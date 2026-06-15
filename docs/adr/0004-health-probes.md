# ADR-0004: Extended health probes

**Status:** Accepted  
**Date:** 2026-06-15

## Context

Laravel `/up` only verifies the application bootstraps. Operators and load balancers need dependency checks.

## Decision

- Keep framework `/up` for minimal liveness.
- Add `/health` JSON endpoint with probes: database, Redis, cache R/W, storage R/W, queue depth.
- Return HTTP 200 when all probes pass, 503 otherwise.
- Include `X-Request-Id` on all HTTP responses (see structured logging).

## Consequences

- `/health` is safe for internal monitoring; do not expose sensitive error details publicly without auth in production.
