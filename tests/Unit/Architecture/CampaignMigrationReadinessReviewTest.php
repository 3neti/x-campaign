<?php

declare(strict_types=1);

it('documents migration readiness before schema files are introduced', function () {
    $path = __DIR__.'/../../../docs/phase-2-migration-readiness.md';

    expect(is_file($path))->toBeTrue();

    $document = file_get_contents($path) ?: '';

    expect($document)
        ->toContain('# Phase 2 Migration Readiness Review')
        ->toContain('## Required Tables')
        ->toContain('campaign_plans')
        ->toContain('campaign_audiences')
        ->toContain('campaign_recipients')
        ->toContain('campaign_imports')
        ->toContain('campaign_import_rows')
        ->toContain('## Required Portable Identifiers')
        ->toContain('planning_key')
        ->toContain('campaign_id')
        ->toContain('audience_id')
        ->toContain('recipient_id')
        ->toContain('import_id')
        ->toContain('row_id')
        ->toContain('## Required Indexes')
        ->toContain('unique planning_key')
        ->toContain('unique campaign_id')
        ->toContain('campaign_id + audience_id')
        ->toContain('audience_id + recipient_id')
        ->toContain('audience_id + import_id')
        ->toContain('import_id + row_id')
        ->toContain('## JSON Columns')
        ->toContain('metadata')
        ->toContain('effects')
        ->toContain('source_payload')
        ->toContain('review_payload')
        ->toContain('## Not Authorized In Phase 2C')
        ->toContain('No migrations')
        ->toContain('No queues')
        ->toContain('No Pay Code generation')
        ->toContain('No delivery')
        ->toContain('No journal writes')
        ->toContain('No money movement');
});

it('records that phase two c authorized the later migration baseline without owning runtime effects', function () {
    $document = file_get_contents(__DIR__.'/../../../docs/phase-2-migration-readiness.md') ?: '';

    expect($document)
        ->toContain('Phase 2D may proceed')
        ->toContain('tests continue proving no queues, delivery, issuance, journal writes, or money movement');
});
