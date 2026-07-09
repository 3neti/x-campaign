<?php

declare(strict_types=1);

it('documents the phase 11 observability and operational hardening boundary before exporters exist', function () {
    $root = dirname(__DIR__, 3);
    $document = $root.'/docs/phase-11-observability-operational-hardening-boundary.md';

    expect(file_exists($document))->toBeTrue();

    $contents = file_get_contents($document);

    expect($contents)->toContain('# Phase 11 Campaign Observability / Operational Hardening Boundary')
        ->and($contents)->toContain('read-only health snapshots')
        ->and($contents)->toContain('operator diagnostics')
        ->and($contents)->toContain('No metrics exporter')
        ->and($contents)->toContain('No alert delivery')
        ->and($contents)->toContain('No journal writes')
        ->and($contents)->toContain('No queue workers')
        ->and($contents)->toContain('Phase 11A — Observability / Operational Hardening Boundary Plan')
        ->and($contents)->toContain('Phase 11F — Observability / Operational Hardening Parity');
});

it('does not introduce concrete observability transports during the phase 11 boundary plan', function () {
    $root = dirname(__DIR__, 3);

    expect(is_dir($root.'/src/Metrics'))->toBeFalse()
        ->and(is_dir($root.'/src/Alerts'))->toBeFalse()
        ->and(is_dir($root.'/src/Loggers'))->toBeFalse()
        ->and(is_dir($root.'/src/Monitoring'))->toBeFalse()
        ->and(is_dir($root.'/src/Listeners'))->toBeFalse();
});
