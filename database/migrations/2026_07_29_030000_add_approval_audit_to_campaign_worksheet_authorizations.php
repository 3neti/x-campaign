<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('campaign_worksheet_authorizations', function (Blueprint $table): void {
            $table->string('approved_by_type', 191)->nullable()->after('approval_pay_code');
            $table->string('approved_by_id', 191)->nullable()->after('approved_by_type');
            $table->timestamp('approved_at')->nullable()->after('approved_by_id');
        });
    }

    public function down(): void
    {
        Schema::table('campaign_worksheet_authorizations', function (Blueprint $table): void {
            $table->dropColumn(['approved_by_type', 'approved_by_id', 'approved_at']);
        });
    }
};
