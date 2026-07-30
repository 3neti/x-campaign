<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class CampaignWorksheetIntake extends Model
{
    protected $fillable = [
        'owner_type',
        'owner_id',
        'status',
        'source_name_ciphertext',
        'source_format',
        'content_hash',
        'row_count',
        'source_manifest_ciphertext',
        'mapping',
        'suggestion',
        'converted_worksheet_reference',
        'converted_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $intake): void {
            $intake->reference ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'source_manifest_ciphertext' => 'encrypted:array',
            'source_name_ciphertext' => 'encrypted',
            'mapping' => 'array',
            'suggestion' => 'array',
            'converted_at' => 'immutable_datetime',
        ];
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    public function rows(): HasMany
    {
        return $this->hasMany(CampaignWorksheetIntakeRow::class);
    }
}
