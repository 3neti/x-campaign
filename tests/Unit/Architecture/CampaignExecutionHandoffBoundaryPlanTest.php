<?php

declare(strict_types=1);

it('documents the phase four execution handoff boundary before execution handoff contracts are introduced', function () {
    $path = __DIR__.'/../../../docs/phase-4-execution-handoff-boundary.md';

    expect(is_file($path))->toBeTrue();

    $document = file_get_contents($path) ?: '';

    expect($document)
        ->toContain('# Phase 4 Execution Handoff Boundary')
        ->toContain('## Handoff Ownership')
        ->toContain('## Explicit Non-Goals')
        ->toContain('No Pay Code generation')
        ->toContain('No delivery')
        ->toContain('No journal writes')
        ->toContain('No provider calls')
        ->toContain('No money movement')
        ->toContain('## Phase 4 Slice Sequence')
        ->toContain('Phase 4B')
        ->toContain('Phase 4C')
        ->toContain('Phase 4D')
        ->toContain('Phase 4E')
        ->toContain('Phase 4F');
});

it('keeps phase four free of execution side effect infrastructure', function () {
    $root = realpath(__DIR__.'/../../..');

    expect(is_dir($root.'/src/Handoff'))->toBeFalse()
        ->and(is_dir($root.'/src/Delivery'))->toBeFalse()
        ->and(is_dir($root.'/src/Journal'))->toBeFalse()
        ->and(is_dir($root.'/src/Providers'))->toBeFalse();
});
