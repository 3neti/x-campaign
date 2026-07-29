<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_worksheets', function (Blueprint $table): void {
            $table->id();
            $table->ulid('reference')->unique();
            $table->string('owner_type', 191);
            $table->string('owner_id', 191);
            $table->string('profile', 32);
            $table->string('name', 160);
            $table->string('currency', 3)->default('PHP');
            $table->string('status', 32)->default('draft');
            $table->string('pay_code_template_reference', 26)->nullable();
            $table->string('fulfillment_mode', 48)->default('pay_code_distribution');
            $table->json('delivery_plan')->nullable();
            $table->json('metadata')->nullable();
            $table->string('rows_hash', 64)->nullable();
            $table->timestamp('frozen_at')->nullable();
            $table->timestamps();

            $table->index(
                ['owner_type', 'owner_id', 'status'],
                'campaign_worksheets_owner_status_index',
            );
            $table->index(['status', 'updated_at'], 'campaign_worksheets_status_updated_index');
        });

        Schema::create('campaign_worksheet_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_worksheet_id')->constrained('campaign_worksheets')->cascadeOnDelete();
            $table->ulid('reference')->unique();
            $table->unsignedInteger('ordinal');
            $table->longText('beneficiary_ciphertext');
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency', 3)->default('PHP');
            $table->string('delivery_preference', 32)->nullable();
            $table->string('status', 32)->default('draft');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['campaign_worksheet_id', 'ordinal'], 'campaign_worksheet_rows_worksheet_ordinal_unique');
            $table->index(['campaign_worksheet_id', 'status'], 'campaign_worksheet_rows_worksheet_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_worksheet_rows');
        Schema::dropIfExists('campaign_worksheets');
    }
};
