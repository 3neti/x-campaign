<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_plans', function (Blueprint $table): void {
            $table->id();
            $table->string('planning_key')->unique();
            $table->string('campaign_id')->unique();
            $table->string('name')->nullable();
            $table->string('status')->default('draft')->index();
            $table->json('metadata')->nullable();
            $table->json('effects')->nullable();
            $table->timestamps();
        });

        Schema::create('campaign_audiences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_plan_id')->constrained('campaign_plans')->cascadeOnDelete();
            $table->string('campaign_id')->index();
            $table->string('audience_id');
            $table->string('name')->nullable();
            $table->string('status')->default('draft')->index();
            $table->json('metadata')->nullable();
            $table->json('effects')->nullable();
            $table->timestamps();

            $table->unique(['campaign_id', 'audience_id']);
        });

        Schema::create('campaign_recipients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_audience_id')->constrained('campaign_audiences')->cascadeOnDelete();
            $table->string('audience_id')->index();
            $table->string('recipient_id');
            $table->string('name')->nullable();
            $table->string('status')->default('planned')->index();
            $table->json('metadata')->nullable();
            $table->json('effects')->nullable();
            $table->timestamps();

            $table->unique(['audience_id', 'recipient_id']);
        });

        Schema::create('campaign_imports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_audience_id')->constrained('campaign_audiences')->cascadeOnDelete();
            $table->string('audience_id')->index();
            $table->string('import_id');
            $table->string('status')->default('planned')->index();
            $table->json('source_payload')->nullable();
            $table->json('review_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->json('effects')->nullable();
            $table->timestamps();

            $table->unique(['audience_id', 'import_id']);
        });

        Schema::create('campaign_import_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_import_id')->constrained('campaign_imports')->cascadeOnDelete();
            $table->string('import_id')->index();
            $table->string('row_id');
            $table->string('status')->default('planned')->index();
            $table->json('source_payload')->nullable();
            $table->json('review_payload')->nullable();
            $table->json('metadata')->nullable();
            $table->json('effects')->nullable();
            $table->timestamps();

            $table->unique(['import_id', 'row_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_import_rows');
        Schema::dropIfExists('campaign_imports');
        Schema::dropIfExists('campaign_recipients');
        Schema::dropIfExists('campaign_audiences');
        Schema::dropIfExists('campaign_plans');
    }
};
