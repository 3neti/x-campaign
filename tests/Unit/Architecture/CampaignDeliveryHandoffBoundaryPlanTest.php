<?php

declare(strict_types=1);

it('documents the phase six delivery feedback handoff boundary before delivery planning is introduced', function () {
    $path = __DIR__.'/../../../docs/phase-6-delivery-feedback-handoff-boundary.md';

    expect(is_file($path))->toBeTrue();

    $document = file_get_contents($path) ?: '';

    expect($document)
        ->toContain('# Phase 6 Delivery / Feedback Handoff Boundary')
        ->toContain('## Purpose')
        ->toContain('## Ownership')
        ->toContain('## Explicit Non-Goals')
        ->toContain('No direct x-feedback package dependency')
        ->toContain('No notification delivery')
        ->toContain('No provider calls')
        ->toContain('No journal writes')
        ->toContain('No Pay Code issuance')
        ->toContain('No money movement')
        ->toContain('## Phase 6 Slice Sequence')
        ->toContain('Phase 6B')
        ->toContain('Phase 6C')
        ->toContain('Phase 6D')
        ->toContain('Phase 6E')
        ->toContain('Phase 6F');
});

it('keeps phase six boundary free of real delivery infrastructure', function () {
    $root = realpath(__DIR__.'/../../..');

    expect(is_dir($root.'/src/Notifications'))->toBeFalse()
        ->and(is_dir($root.'/src/Mail'))->toBeFalse()
        ->and(is_dir($root.'/src/Http/Clients'))->toBeFalse()
        ->and(is_dir($root.'/src/Http/Controllers'))->toBeFalse();
});
