<?php

declare(strict_types=1);

it('documents the phase 14 public api boundary before package routes exist', function () {
    $document = dirname(__DIR__, 3).'/docs/phase-14-public-api-boundary.md';

    expect(file_exists($document))->toBeTrue();

    $contents = file_get_contents($document);

    expect($contents)->toContain('# Phase 14 Campaign Public API Boundary')
        ->and($contents)->toContain('public API descriptors')
        ->and($contents)->toContain('host-owned API routes and controllers')
        ->and($contents)->toContain('No route registration')
        ->and($contents)->toContain('No controller registration')
        ->and($contents)->toContain('No request validation ownership')
        ->and($contents)->toContain('Phase 14A — Public API Boundary Plan')
        ->and($contents)->toContain('Phase 14F — Public API Parity');
});

it('does not introduce concrete public api infrastructure in phase 14', function () {
    $root = dirname(__DIR__, 3);

    expect(is_dir($root.'/src/Http'))->toBeFalse()
        ->and(is_dir($root.'/src/Routes'))->toBeFalse()
        ->and(is_dir($root.'/src/Controllers'))->toBeFalse()
        ->and(is_dir($root.'/src/Requests'))->toBeFalse()
        ->and(is_dir($root.'/src/Resources'))->toBeFalse()
        ->and(is_dir($root.'/routes'))->toBeFalse();
});
