<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign;

use Illuminate\Support\ServiceProvider;
use LBHurtado\XCampaign\Actions\ArchiveCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\ScheduleCampaignPlan;
use LBHurtado\XCampaign\Actions\UpdateCampaignPlan;
use LBHurtado\XCampaign\Contracts\ArchivesCampaignPlans;
use LBHurtado\XCampaign\Contracts\CampaignFeatureProfileResolver;
use LBHurtado\XCampaign\Contracts\CreatesCampaignPlans;
use LBHurtado\XCampaign\Contracts\SchedulesCampaignPlans;
use LBHurtado\XCampaign\Contracts\UpdatesCampaignPlans;
use LBHurtado\XCampaign\Services\CampaignStateGrammar;
use LBHurtado\XCampaign\Services\ConfigCampaignFeatureProfileResolver;

class XCampaignServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/x-campaign.php', 'x-campaign');

        $this->app->singleton(
            CampaignFeatureProfileResolver::class,
            ConfigCampaignFeatureProfileResolver::class,
        );

        $this->app->singleton(CampaignStateGrammar::class);
        $this->app->singleton(CreatesCampaignPlans::class, CreateCampaignPlan::class);
        $this->app->singleton(UpdatesCampaignPlans::class, UpdateCampaignPlan::class);
        $this->app->singleton(SchedulesCampaignPlans::class, ScheduleCampaignPlan::class);
        $this->app->singleton(ArchivesCampaignPlans::class, ArchiveCampaignPlan::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/x-campaign.php' => config_path('x-campaign.php'),
            ], 'x-campaign-config');
        }
    }
}
