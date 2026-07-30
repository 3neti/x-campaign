<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_worksheet_import_rows', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_worksheet_import_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedInteger('source_row');
            $table->string('status', 32);
            $table->longText('source_ciphertext');
            $table->longText('normalized_ciphertext')->nullable();
            $table->longText('errors_ciphertext')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
            $table->unique(
                ['campaign_worksheet_import_id', 'source_row'],
                'campaign_import_source_row_unique',
            );
            $table->index(
                ['campaign_worksheet_import_id', 'status'],
                'campaign_import_row_status_index',
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_worksheet_import_rows');
    }
};
