<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_worksheet_intakes', function (Blueprint $table): void {
            $table->id();
            $table->ulid('reference')->unique();
            $table->morphs('owner');
            $table->string('status', 32)->default('staged');
            $table->text('source_name_ciphertext');
            $table->string('source_format', 16);
            $table->string('content_hash', 64);
            $table->unsignedInteger('row_count');
            $table->longText('source_manifest_ciphertext');
            $table->json('mapping');
            $table->json('suggestion');
            $table->string('converted_worksheet_reference', 26)->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->timestamps();
            $table->index(['owner_type', 'owner_id', 'status'], 'campaign_intake_owner_status_index');
            $table->index(['owner_type', 'owner_id', 'content_hash'], 'campaign_intake_owner_hash_index');
        });

        Schema::create('campaign_worksheet_intake_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_worksheet_intake_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('source_row');
            $table->string('status', 32);
            $table->longText('source_ciphertext');
            $table->longText('normalized_ciphertext')->nullable();
            $table->longText('errors_ciphertext')->nullable();
            $table->timestamps();
            $table->unique(
                ['campaign_worksheet_intake_id', 'source_row'],
                'campaign_intake_source_row_unique',
            );
            $table->index(
                ['campaign_worksheet_intake_id', 'status'],
                'campaign_intake_row_status_index',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_worksheet_intake_rows');
        Schema::dropIfExists('campaign_worksheet_intakes');
    }
};
