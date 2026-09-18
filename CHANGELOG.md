# Changelog

## v1.1.0 - 2026-09-18

- Add endpoint campaign model, availability policy, bounded owner read model, and persistence repository.
- Support consuming model subclasses, public slug lookup, creation, and post-issuance start recording without changing execution ownership.
- Preserve existing table identities, events, public endpoints, and caller transactions; add regression coverage and extraction plan/compass.

## v1.0.0 - 2026-07-31

- Introduce encrypted campaign worksheets, staged CSV/XLSX imports, validation, freezing, and authorization records.
- Add provider-neutral fulfillment planning for Pay Code distribution and direct bank transfer fallbacks.
- Add beneficiary results, exports, delivery handoffs, claim visibility, and campaign activity projections.
- Preserve side-effect boundaries between campaign planning and settlement execution.
