<?php

declare(strict_types=1);

it('documents the phase five pay code generation gateway boundary before generation planning is introduced', function () {
    $path = __DIR__.'/../../../docs/phase-5-pay-code-generation-boundary.md';

    expect(is_file($path))->toBeTrue();

    $document = file_get_contents($path) ?: '';

    expect($document)
        ->toContain('# Phase 5 Pay Code Generation Gateway Boundary')
        ->toContain('## Purpose')
        ->toContain('## Ownership')
        ->toContain('## Explicit Non-Goals')
        ->toContain('No direct voucher package dependency')
        ->toContain('No x-change concrete dependency')
        ->toContain('No wallet mutation')
        ->toContain('No provider calls')
        ->toContain('No notification delivery')
        ->toContain('No journal writes')
        ->toContain('No money movement')
        ->toContain('## Phase 5 Slice Sequence')
        ->toContain('Phase 5B')
        ->toContain('Phase 5C')
        ->toContain('Phase 5D')
        ->toContain('Phase 5E')
        ->toContain('Phase 5F');
});

it('keeps phase five boundary free of real gateway infrastructure', function () {
    $root = realpath(__DIR__.'/../../..');

    expect(is_dir($root.'/src/Providers'))->toBeFalse()
        ->and(is_dir($root.'/src/Wallets'))->toBeFalse()
        ->and(is_dir($root.'/src/Http/Controllers'))->toBeFalse();
});
