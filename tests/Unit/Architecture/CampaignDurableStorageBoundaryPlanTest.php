<?php

declare(strict_types=1);

it('documents the phase two durable storage boundary before migrations exist', function () {
    $path = __DIR__.'/../../../docs/phase-2-durable-storage-boundary.md';

    expect(is_file($path))->toBeTrue();

    $document = file_get_contents($path) ?: '';

    expect($document)
        ->toContain('# Phase 2 Durable Storage Boundary')
        ->toContain('## Storage Ownership')
        ->toContain('## Proposed Tables')
        ->toContain('campaign_plans')
        ->toContain('campaign_audiences')
        ->toContain('campaign_recipients')
        ->toContain('campaign_imports')
        ->toContain('campaign_import_rows')
        ->toContain('## Explicit Non-Goals')
        ->toContain('No queues')
        ->toContain('No Pay Code generation')
        ->toContain('No delivery')
        ->toContain('No journal writes')
        ->toContain('No money movement')
        ->toContain('## Phase 2 Slice Sequence')
        ->toContain('Phase 2B')
        ->toContain('Phase 2C')
        ->toContain('Phase 2D')
        ->toContain('Phase 2E')
        ->toContain('Phase 2F');
});

it('keeps phase two a free of migrations while storage is still only planned', function () {
    $migrationFiles = glob(__DIR__.'/../../../database/migrations/*.php') ?: [];

    expect($migrationFiles)->toBe([]);
});
