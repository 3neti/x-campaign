<?php

declare(strict_types=1);

it('documents the phase eight analytics reporting boundary before aggregation is introduced', function () {
    $path = __DIR__.'/../../../docs/phase-8-analytics-reporting-boundary.md';

    expect(is_file($path))->toBeTrue();

    $document = file_get_contents($path) ?: '';

    expect($document)
        ->toContain('# Phase 8 Analytics / Reporting Aggregation Boundary')
        ->toContain('## Purpose')
        ->toContain('## Ownership')
        ->toContain('## Explicit Non-Goals')
        ->toContain('No lifecycle truth ownership')
        ->toContain('No claim lifecycle mutation')
        ->toContain('No notification delivery')
        ->toContain('No journal writes')
        ->toContain('No provider calls')
        ->toContain('No money movement')
        ->toContain('## Phase 8 Slice Sequence')
        ->toContain('Phase 8B')
        ->toContain('Phase 8C')
        ->toContain('Phase 8D')
        ->toContain('Phase 8E')
        ->toContain('Phase 8F');
});

it('keeps phase eight boundary free of reporting transport surfaces', function () {
    $root = realpath(__DIR__.'/../../..');

    expect(is_dir($root.'/src/Http/Controllers'))->toBeFalse()
        ->and(is_dir($root.'/src/Exports'))->toBeFalse()
        ->and(is_dir($root.'/src/Reports'))->toBeFalse()
        ->and(is_dir($root.'/src/Dashboards'))->toBeFalse();
});
