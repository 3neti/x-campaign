<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignPublicApiWorkspace;
use LBHurtado\XCampaign\Data\CampaignPublicApiDescriptorData;
use LBHurtado\XCampaign\Workspaces\RepositoryBackedCampaignPublicApiWorkspace;

it('defines the public api workspace contract', function () {
    expect(interface_exists(CampaignPublicApiWorkspace::class))->toBeTrue()
        ->and(method_exists(CampaignPublicApiWorkspace::class, 'descriptor'))->toBeTrue()
        ->and(method_exists(CampaignPublicApiWorkspace::class, 'effects'))->toBeTrue();
});

it('repository-backed public api workspace implements the contract', function () {
    expect(class_exists(RepositoryBackedCampaignPublicApiWorkspace::class))->toBeTrue()
        ->and(RepositoryBackedCampaignPublicApiWorkspace::class)
        ->toImplement(CampaignPublicApiWorkspace::class);
});

it('declares descriptor return semantics', function () {
    $method = new ReflectionMethod(CampaignPublicApiWorkspace::class, 'descriptor');

    expect($method->getReturnType()?->getName())->toBe(CampaignPublicApiDescriptorData::class);
});
