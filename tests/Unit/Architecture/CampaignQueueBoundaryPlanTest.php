<?php

declare(strict_types=1);

it('documents the phase three queue boundary before queue contracts are introduced', function () {
    $path = __DIR__.'/../../../docs/phase-3-queue-boundary.md';

    expect(is_file($path))->toBeTrue();

    $document = file_get_contents($path) ?: '';

    expect($document)
        ->toContain('# Phase 3 Queue Boundary')
        ->toContain('## Queue Ownership')
        ->toContain('## Explicit Non-Goals')
        ->toContain('No Pay Code generation')
        ->toContain('No delivery')
        ->toContain('No journal writes')
        ->toContain('No money movement')
        ->toContain('## Phase 3 Slice Sequence')
        ->toContain('Phase 3B')
        ->toContain('Phase 3C')
        ->toContain('Phase 3D')
        ->toContain('Phase 3E')
        ->toContain('Phase 3F');
});

it('keeps phase three a free of queue job classes and dispatch behavior', function () {
    $root = realpath(__DIR__.'/../../..');
    $source = collect([
        ...glob($root.'/src/**/*.php') ?: [],
        ...glob($root.'/src/**/**/*.php') ?: [],
    ])->map(fn (string $file): string => file_get_contents($file) ?: '')->implode("\n");

    expect(is_dir($root.'/src/Jobs'))->toBeFalse()
        ->and($source)
        ->not->toContain('ShouldQueue')
        ->not->toContain('Bus::')
        ->not->toContain('dispatch(')
        ->not->toContain('Queue::');
});
