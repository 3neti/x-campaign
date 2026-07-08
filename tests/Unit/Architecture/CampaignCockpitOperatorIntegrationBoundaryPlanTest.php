<?php

declare(strict_types=1);

it('documents the phase 10 cockpit and operator api integration boundary before routes exist', function () {
    $root = dirname(__DIR__, 3);
    $document = $root.'/docs/phase-10-cockpit-operator-integration-boundary.md';

    expect(file_exists($document))->toBeTrue();

    $contents = file_get_contents($document);

    expect($contents)->toContain('# Phase 10 Campaign Cockpit / Operator API Integration Boundary')
        ->and($contents)->toContain('read-only Cockpit summaries')
        ->and($contents)->toContain('operator API response envelopes')
        ->and($contents)->toContain('No routes')
        ->and($contents)->toContain('No controllers')
        ->and($contents)->toContain('No campaign mutation')
        ->and($contents)->toContain('No Pay Code issuance')
        ->and($contents)->toContain('No feedback delivery')
        ->and($contents)->toContain('No journal writes')
        ->and($contents)->toContain('Phase 10A — Cockpit / Operator Integration Boundary Plan')
        ->and($contents)->toContain('Phase 10F — Cockpit / Operator Integration Parity');
});

it('does not introduce concrete cockpit routes controllers or pages during the phase 10 boundary plan', function () {
    $root = dirname(__DIR__, 3);

    expect(is_dir($root.'/src/Http'))->toBeFalse()
        ->and(is_dir($root.'/src/Controllers'))->toBeFalse()
        ->and(is_dir($root.'/src/Routes'))->toBeFalse()
        ->and(is_dir($root.'/resources/js'))->toBeFalse()
        ->and(is_dir($root.'/resources/views'))->toBeFalse();
});
