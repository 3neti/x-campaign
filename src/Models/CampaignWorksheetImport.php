<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CampaignWorksheetImport extends Model
{
    protected $fillable = ['campaign_worksheet_id', 'status', 'source_format', 'content_hash', 'row_count', 'rows_ciphertext', 'mapping'];

    protected static function booted(): void
    {
        static::creating(function (self $import): void {
            $import->reference ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return ['rows_ciphertext' => 'encrypted:array', 'mapping' => 'array'];
    }

    public function worksheet(): BelongsTo
    {
        return $this->belongsTo(CampaignWorksheet::class, 'campaign_worksheet_id');
    }

    public function rows(): HasMany
    {
        return $this->hasMany(
            CampaignWorksheetImportRow::class,
            'campaign_worksheet_import_id',
        );
    }
}
