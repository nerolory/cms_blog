# ADR-0003: Domain events for post lifecycle

**Status:** Accepted  
**Date:** 2026-06-15

## Context

Post lifecycle side-effects (moderation alerts, structured audit logs) must not be scattered across controllers.

## Decision

`PostMutationPipeline` dispatches:

| Event | When |
|-------|------|
| `PostSubmitted` | Author submission / re-submission |
| `PostUpdated` | Any successful update |
| `PostPublished` | Transition to published status |

Listeners handle notifications and structured logging. Cache invalidation stays in the pipeline to avoid duplicate bumps.

## Consequences

- Listeners are synchronous by default; heavy work uses queued notifications.
- New side-effects attach via listeners, not pipeline branches.
