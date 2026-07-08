<?php

declare(strict_types=1);

it('documents the phase five portable code generation as-built architecture', function () {
    $document = file_get_contents(__DIR__.'/../../../docs/x-campaign-architecture.md') ?: '';

    expect($document)
        ->toContain('## Phase 5A Boundary')
        ->toContain('## Phase 5B Boundary')
        ->toContain('## Phase 5C Boundary')
        ->toContain('## Phase 5D Boundary')
        ->toContain('## Phase 5E Boundary')
        ->toContain('## Phase 5F Boundary')
        ->toContain('portable-code generation read model');
});

it('keeps phase five generation architecture free of runtime issuance side effects', function () {
    $root = realpath(__DIR__.'/../../..');
    $sourceFiles = iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/src')));
    $source = '';

    foreach ($sourceFiles as $file) {
        if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
            $source .= file_get_contents($file->getPathname()) ?: '';
        }
    }

    expect($source)
        ->not->toContain('LBHurtado\\XChange')
        ->not->toContain('LBHurtado\\Voucher')
        ->not->toContain('Bavix\\Wallet')
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Http::')
        ->not->toContain('DB::transaction')
        ->not->toContain('dispatchSync(');
});

