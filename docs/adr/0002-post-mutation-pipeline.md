# ADR-0002: PostMutationPipeline as single write path

**Status:** Accepted  
**Date:** 2026-06-15

## Context

Posts were previously created/updated from multiple places (web, API, Filament) with duplicated side-effects.

## Decision

All post mutations (create, update, moderate, restore, media sync) go through `PostMutationPipeline` via `PostService`.

Filament Create/Edit pages call `PostServiceContract`, never raw `$post->update()`.

## Consequences

- Domain events, moderation logs, version snapshots, and cache bumps happen consistently.
- New mutation types must extend the pipeline, not add ad-hoc repository calls in UI layers.
