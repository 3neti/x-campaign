<?php

declare(strict_types=1);

use LBHurtado\XCampaign\Contracts\CampaignFeatureProfileResolver;

it('boots the package service provider and resolves the feature profile resolver', function () {
    $resolver = app(CampaignFeatureProfileResolver::class);

    $profile = $resolver->resolve();

    expect($profile->name)->toBe('baseline')
        ->and($profile->metadata['source'])->toBe('config')
        ->and($profile->metadata['owns_execution'])->toBeFalse()
        ->and($profile->metadata['owns_notification_transport'])->toBeFalse();
});

it('allows feature profile default to come from config', function () {
    config()->set('x-campaign.feature_profile.default', 'campaign-core');

    $profile = app(CampaignFeatureProfileResolver::class)->resolve();

    expect($profile->name)->toBe('campaign-core');
});

