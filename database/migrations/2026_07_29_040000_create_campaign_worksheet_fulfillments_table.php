<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_worksheet_fulfillments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_worksheet_authorization_id')->constrained('campaign_worksheet_authorizations')->cascadeOnDelete();
            $table->foreignId('campaign_worksheet_row_id')->constrained('campaign_worksheet_rows')->cascadeOnDelete();
            $table->ulid('reference')->unique();
            $table->string('mode', 48);
            $table->string('status', 32)->default('planned');
            $table->string('pay_code', 64)->nullable()->unique();
            $table->string('provider_transfer_reference', 128)->nullable()->unique();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['campaign_worksheet_authorization_id', 'campaign_worksheet_row_id'], 'campaign_fulfillment_authorization_row_unique');
            $table->index(['status', 'mode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_worksheet_fulfillments');
    }
};
