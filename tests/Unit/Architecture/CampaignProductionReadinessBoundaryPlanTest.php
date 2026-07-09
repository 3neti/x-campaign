<?php

declare(strict_types=1);

it('documents the phase 12 production readiness boundary before implementation expands', function () {
    $document = dirname(__DIR__, 3).'/docs/phase-12-production-readiness-boundary.md';

    expect(file_exists($document))->toBeTrue();

    $contents = file_get_contents($document);

    expect($contents)->toContain('# Phase 12 Campaign Production Readiness Boundary')
        ->and($contents)->toContain('read-only production readiness assessments')
        ->and($contents)->toContain('release readiness envelopes')
        ->and($contents)->toContain('No deployment automation')
        ->and($contents)->toContain('No environment writes')
        ->and($contents)->toContain('No queue workers')
        ->and($contents)->toContain('No migrations')
        ->and($contents)->toContain('Phase 12A — Production Readiness Boundary Plan')
        ->and($contents)->toContain('Phase 12F — Production Readiness Parity');
});

it('does not introduce deployment or environment mutation infrastructure in phase 12', function () {
    $root = dirname(__DIR__, 3);

    expect(is_dir($root.'/src/Deployments'))->toBeFalse()
        ->and(is_dir($root.'/src/Releases'))->toBeFalse()
        ->and(is_dir($root.'/src/EnvironmentWriters'))->toBeFalse()
        ->and(is_dir($root.'/src/Installers'))->toBeFalse()
        ->and(is_dir($root.'/src/Provisioning'))->toBeFalse();
});
