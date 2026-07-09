<?php

declare(strict_types=1);

it('documents the phase 13 host integration boundary before package endpoints exist', function () {
    $document = dirname(__DIR__, 3).'/docs/phase-13-host-integration-boundary.md';

    expect(file_exists($document))->toBeTrue();

    $contents = file_get_contents($document);

    expect($contents)->toContain('# Phase 13 Campaign Host Integration Boundary')
        ->and($contents)->toContain('host-safe integration manifests')
        ->and($contents)->toContain('host-owned routes and controllers')
        ->and($contents)->toContain('No route registration')
        ->and($contents)->toContain('No controller registration')
        ->and($contents)->toContain('No middleware or policy ownership')
        ->and($contents)->toContain('Phase 13A — Host Integration Boundary Plan')
        ->and($contents)->toContain('Phase 13F — Host Integration Parity');
});

it('does not introduce host route controller middleware or policy infrastructure in phase 13', function () {
    $root = dirname(__DIR__, 3);

    expect(is_dir($root.'/src/Http'))->toBeFalse()
        ->and(is_dir($root.'/src/Routes'))->toBeFalse()
        ->and(is_dir($root.'/src/Controllers'))->toBeFalse()
        ->and(is_dir($root.'/src/Middleware'))->toBeFalse()
        ->and(is_dir($root.'/src/Policies'))->toBeFalse()
        ->and(is_dir($root.'/routes'))->toBeFalse();
});
