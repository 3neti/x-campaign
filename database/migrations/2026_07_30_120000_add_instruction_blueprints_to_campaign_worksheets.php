<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_worksheets', function (Blueprint $table): void {
            $table->longText('instruction_blueprint_ciphertext')->nullable();
            $table->string('instruction_blueprint_hash', 64)->nullable()->index();
            $table->string('instruction_blueprint_schema', 96)->nullable();
            $table->unsignedInteger('instruction_blueprint_revision')->default(0);
            $table->string('manifest_hash', 64)->nullable()->index();
        });

        Schema::table('campaign_worksheet_authorizations', function (Blueprint $table): void {
            $table->longText('instruction_blueprint_ciphertext')->nullable();
            $table->string('instruction_blueprint_hash', 64)->nullable();
            $table->string('instruction_blueprint_schema', 96)->nullable();
            $table->string('rows_hash', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('campaign_worksheet_authorizations', function (Blueprint $table): void {
            $table->dropColumn([
                'instruction_blueprint_ciphertext',
                'instruction_blueprint_hash',
                'instruction_blueprint_schema',
                'rows_hash',
            ]);
        });

        Schema::table('campaign_worksheets', function (Blueprint $table): void {
            $table->dropIndex(['instruction_blueprint_hash']);
            $table->dropIndex(['manifest_hash']);
            $table->dropColumn([
                'instruction_blueprint_ciphertext',
                'instruction_blueprint_hash',
                'instruction_blueprint_schema',
                'instruction_blueprint_revision',
                'manifest_hash',
            ]);
        });
    }
};
