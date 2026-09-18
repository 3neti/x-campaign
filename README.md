# x-campaign

`x-campaign` is the beneficiary distribution platform for the x-change Settlement Operating System.

It owns campaign, audience, recipient, distribution, engagement, attribution, and analytics scaffolding.

It does not own voucher execution, claim lifecycle, disbursement, settlement, notification transport, wallet mutation, or Pay Code generation.

## Install

```bash
composer require 3neti/x-campaign
```

Laravel discovers `XCampaignServiceProvider` automatically. Publish and run the package migrations through the consuming application's normal deployment process.

## Responsibilities

- encrypted beneficiary worksheet staging and import;
- worksheet validation, freezing, and authorization evidence;
- provider-neutral fulfillment plans and beneficiary result projections;
- portable Pay Code generation, delivery handoff, and claim-visibility planning;
- append-only campaign activity and export-ready read models.

Runtime execution remains in the consuming settlement application. Planning services never send notifications, move funds, execute provider calls, or issue Pay Codes directly.

## Campaign management extraction

The endpoint-management workstream is tracked in the
[implementation plan](docs/CAMPAIGN_MANAGEMENT_PLAN.md) and its
[living compass](docs/CAMPAIGN_MANAGEMENT_COMPASS.md). Consult the compass before
continuing this work and update it with each implementation or release gate.

## Compatibility

- PHP 8.3 or 8.4
- Laravel 12 or 13
- Pest 3 or 4 for package development

## Quality gates

```bash
composer validate --strict
vendor/bin/pint --test
composer test
composer audit
```
