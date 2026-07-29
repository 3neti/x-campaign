<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_worksheet_imports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_worksheet_id')->constrained()->cascadeOnDelete();
            $table->ulid('reference')->unique();
            $table->string('status', 32)->default('staged');
            $table->string('source_format', 16);
            $table->string('content_hash', 64);
            $table->unsignedInteger('row_count');
            $table->text('rows_ciphertext');
            $table->json('mapping');
            $table->timestamps();
            $table->index(['campaign_worksheet_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_worksheet_imports');
    }
};
