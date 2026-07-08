<?php

declare(strict_types=1);

it('documents the phase 9 operator report and export handoff boundary before report transport exists', function () {
    $root = dirname(__DIR__, 3);
    $document = $root.'/docs/phase-9-operator-report-export-boundary.md';

    expect(file_exists($document))->toBeTrue();

    $contents = file_get_contents($document);

    expect($contents)->toContain('# Phase 9 Operator Report / Export Handoff Boundary')
        ->and($contents)->toContain('operator-safe report requests')
        ->and($contents)->toContain('export handoff requests')
        ->and($contents)->toContain('No PDF generation')
        ->and($contents)->toContain('No spreadsheet generation')
        ->and($contents)->toContain('No file storage')
        ->and($contents)->toContain('No report delivery')
        ->and($contents)->toContain('No lifecycle truth ownership')
        ->and($contents)->toContain('Phase 9A — Operator Report / Export Handoff Boundary Plan')
        ->and($contents)->toContain('Phase 9F — Operator Report / Export Parity');
});

it('does not introduce concrete export transports during the phase 9 boundary plan', function () {
    $root = dirname(__DIR__, 3);

    expect(is_dir($root.'/src/Exports'))->toBeFalse()
        ->and(is_dir($root.'/src/ReportTransports'))->toBeFalse()
        ->and(is_dir($root.'/src/Pdf'))->toBeFalse()
        ->and(is_dir($root.'/src/Spreadsheets'))->toBeFalse();
});
