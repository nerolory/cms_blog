# ADR-0001: Service → Repository architecture

**Status:** Accepted  
**Date:** 2026-06-15

## Context

The application needs a clear separation between HTTP/Filament boundaries, business logic, and database access.

## Decision

- Controllers and Filament pages inject **service contracts** only.
- Services orchestrate business rules and call **repository contracts**.
- Repositories are the **only** layer that talks to Eloquent/DB for domain persistence.
- Route model binding (`Post $post`) is allowed in controllers; resolution still flows through services when mutating.

## Consequences

- Easier unit testing via contract mocks.
- Filament read paths may use Eloquent directly; **mutations** must go through `PostMutationPipeline` (see ADR-0002).
