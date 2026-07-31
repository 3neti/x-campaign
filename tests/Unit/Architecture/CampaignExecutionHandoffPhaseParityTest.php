<?php

declare(strict_types=1);

it('documents the phase four execution handoff as-built architecture', function () {
    $document = file_get_contents(__DIR__.'/../../../docs/x-campaign-architecture.md') ?: '';

    expect($document)
        ->toContain('## Phase 4A Boundary')
        ->toContain('## Phase 4B Boundary')
        ->toContain('## Phase 4C Boundary')
        ->toContain('## Phase 4D Boundary')
        ->toContain('## Phase 4E Boundary')
        ->toContain('## Phase 4F Boundary')
        ->toContain('execution handoff read model');
});

it('keeps phase four handoff architecture free of runtime execution side effects', function () {
    $root = realpath(__DIR__.'/../../..');
    $sourceFiles = iterator_to_array(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/src')));
    $source = '';
    $orchestrationSource = '';

    foreach ($sourceFiles as $file) {
        if ($file instanceof SplFileInfo && $file->isFile() && $file->getExtension() === 'php') {
            $source .= file_get_contents($file->getPathname()) ?: '';

            if (! str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'Repositories'.DIRECTORY_SEPARATOR)) {
                $orchestrationSource .= file_get_contents($file->getPathname()) ?: '';
            }
        }
    }

    expect($source)
        ->not->toContain('Notification::')
        ->not->toContain('Mail::')
        ->not->toContain('Http::')
        ->not->toContain('dispatchSync(');

    expect($orchestrationSource)->not->toContain('DB::transaction');
});
