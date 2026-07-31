<?php

declare(strict_types=1);

it('documents the phase seven engagement and claim visibility boundary before visibility planning is introduced', function () {
    $path = __DIR__.'/../../../docs/phase-7-engagement-claim-visibility-boundary.md';

    expect(is_file($path))->toBeTrue();

    $document = file_get_contents($path) ?: '';

    expect($document)
        ->toContain('# Phase 7 Engagement / Claim Visibility Boundary')
        ->toContain('## Purpose')
        ->toContain('## Ownership')
        ->toContain('## Explicit Non-Goals')
        ->toContain('No direct x-change package dependency')
        ->toContain('No voucher redemption')
        ->toContain('No claim lifecycle mutation')
        ->toContain('No provider calls')
        ->toContain('No journal writes')
        ->toContain('No notification delivery')
        ->toContain('No money movement')
        ->toContain('## Phase 7 Slice Sequence')
        ->toContain('Phase 7B')
        ->toContain('Phase 7C')
        ->toContain('Phase 7D')
        ->toContain('Phase 7E')
        ->toContain('Phase 7F');
});

it('keeps phase seven boundary free of host claim runtime surfaces', function () {
    $root = realpath(__DIR__.'/../../..');

    expect(is_dir($root.'/src/Http/Controllers'))->toBeFalse()
        ->and(is_dir($root.'/src/Routes'))->toBeFalse()
        ->and(is_dir($root.'/src/Claims'))->toBeFalse()
        ->and(is_dir($root.'/src/Providers/Claim'))->toBeFalse();
});
