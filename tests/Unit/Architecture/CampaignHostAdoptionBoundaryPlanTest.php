<?php

declare(strict_types=1);

it('documents the Phase 15 host adoption boundary before host-owned integration is implemented', function () {
    $path = dirname(__DIR__, 3).'/docs/phase-15-host-adoption-boundary.md';

    expect(file_exists($path))->toBeTrue();

    $document = file_get_contents($path);

    expect($document)->toContain('# Phase 15 Campaign Host Adoption Boundary')
        ->and($document)->toContain('x-change owns host route registration')
        ->and($document)->toContain('x-change owns controller execution')
        ->and($document)->toContain('x-change owns authorization and redaction')
        ->and($document)->toContain('x-campaign describes safe integration seams')
        ->and($document)->toContain('Phase 15F — Host Adoption Parity Report');
});

it('keeps host adoption free of package-owned transport infrastructure', function () {
    $packageRoot = dirname(__DIR__, 3);

    expect(is_dir($packageRoot.'/src/Http'))->toBeFalse()
        ->and(is_dir($packageRoot.'/src/Routes'))->toBeFalse()
        ->and(is_dir($packageRoot.'/src/Controllers'))->toBeFalse()
        ->and(is_dir($packageRoot.'/src/Requests'))->toBeFalse()
        ->and(is_dir($packageRoot.'/src/Resources'))->toBeFalse()
        ->and(is_dir($packageRoot.'/routes'))->toBeFalse();
});
