# Phase 2 Migration Readiness Review

## Purpose

This document fixes the initial durable schema target before migrations are introduced.

Phase 2C is a review and invariant slice only. It authorizes the future Phase 2D migration baseline, but it does not create migrations.

## Required Tables

The first durable schema baseline should introduce:

- `campaign_plans`
- `campaign_audiences`
- `campaign_recipients`
- `campaign_imports`
- `campaign_import_rows`

These tables represent planning state. They do not represent Pay Code issuance, voucher execution, feedback delivery, journal truth, settlement, wallet state, or money movement.

## Required Portable Identifiers

The schema must preserve these portable identifiers:

- `planning_key`
- `campaign_id`
- `audience_id`
- `recipient_id`
- `import_id`
- `row_id`

Database primary keys may exist for storage mechanics, but DTOs and callers should continue using portable identifiers.

## Required Indexes

The migration baseline should include these lookup constraints:

- unique planning_key on `campaign_plans`
- unique campaign_id on `campaign_plans`
- campaign_id + audience_id on `campaign_audiences`
- audience_id + recipient_id on `campaign_recipients`
- audience_id + import_id on `campaign_imports`
- import_id + row_id on `campaign_import_rows`

## JSON Columns

The initial durable baseline may use JSON columns for flexible context:

- `metadata`
- `effects`
- `source_payload`
- `review_payload`

JSON columns may store presentation, import, and review context. They must not hide lifecycle truth that belongs in explicit identifier or status columns.

## Status Columns

Each durable table should expose explicit status columns where applicable:

- campaign status
- audience status
- recipient status
- import status
- row status

## Not Authorized In Phase 2C

- No migrations
- No queues
- No Pay Code generation
- No delivery
- No journal writes
- No provider calls
- No wallet mutation
- No money movement
- No Cockpit routes
- No file parsing

## Phase 2D Readiness Criteria

Phase 2D may proceed when:

- this review exists
- table names are locked
- portable identifiers are locked
- minimum indexes are locked
- JSON usage is constrained to metadata/context
- tests continue proving no queues, delivery, issuance, journal writes, or money movement
