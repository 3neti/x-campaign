<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaign_worksheet_authorizations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('campaign_worksheet_id')->constrained('campaign_worksheets')->cascadeOnDelete();
            $table->ulid('reference')->unique();
            $table->string('manifest_hash', 64);
            $table->unsignedInteger('beneficiary_count');
            $table->unsignedBigInteger('principal_minor');
            $table->string('currency', 3)->default('PHP');
            $table->string('status', 32)->default('awaiting_officer');
            $table->string('approval_pay_code', 64)->nullable()->unique();
            $table->timestamps();
            $table->unique(['campaign_worksheet_id', 'manifest_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_worksheet_authorizations');
    }
};
