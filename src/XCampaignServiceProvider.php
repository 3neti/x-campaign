<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign;

use Illuminate\Support\ServiceProvider;
use LBHurtado\XCampaign\Actions\AddAudienceToCampaignPlan;
use LBHurtado\XCampaign\Actions\AddRecipientToCampaignAudiencePlan;
use LBHurtado\XCampaign\Actions\ArchiveCampaignPlan;
use LBHurtado\XCampaign\Actions\CreateCampaignPlan;
use LBHurtado\XCampaign\Actions\PlanCampaignExecution;
use LBHurtado\XCampaign\Actions\PlanCampaignExecutionBatches;
use LBHurtado\XCampaign\Actions\RemoveRecipientFromCampaignAudiencePlan;
use LBHurtado\XCampaign\Actions\ScheduleCampaignPlan;
use LBHurtado\XCampaign\Actions\UpdateCampaignPlan;
use LBHurtado\XCampaign\Contracts\AddsAudiencesToCampaignPlans;
use LBHurtado\XCampaign\Contracts\AddsRecipientsToCampaignAudiencePlans;
use LBHurtado\XCampaign\Contracts\ArchivesCampaignPlans;
use LBHurtado\XCampaign\Contracts\BuildsCampaignSummaries;
use LBHurtado\XCampaign\Contracts\CampaignFeatureProfileResolver;
use LBHurtado\XCampaign\Contracts\CreatesCampaignPlans;
use LBHurtado\XCampaign\Contracts\PlansCampaignExecutionBatches;
use LBHurtado\XCampaign\Contracts\PlansCampaignExecutions;
use LBHurtado\XCampaign\Contracts\RemovesRecipientsFromCampaignAudiencePlans;
use LBHurtado\XCampaign\Contracts\SchedulesCampaignPlans;
use LBHurtado\XCampaign\Contracts\UpdatesCampaignPlans;
use LBHurtado\XCampaign\ReadModels\CampaignSummaryReadModel;
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
        $this->app->singleton(AddsAudiencesToCampaignPlans::class, AddAudienceToCampaignPlan::class);
        $this->app->singleton(AddsRecipientsToCampaignAudiencePlans::class, AddRecipientToCampaignAudiencePlan::class);
        $this->app->singleton(RemovesRecipientsFromCampaignAudiencePlans::class, RemoveRecipientFromCampaignAudiencePlan::class);
        $this->app->singleton(PlansCampaignExecutions::class, PlanCampaignExecution::class);
        $this->app->singleton(PlansCampaignExecutionBatches::class, PlanCampaignExecutionBatches::class);
        $this->app->singleton(BuildsCampaignSummaries::class, CampaignSummaryReadModel::class);
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
