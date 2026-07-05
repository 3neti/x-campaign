<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Enums;

use InvalidArgumentException;

enum CampaignStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Running = 'running';
    case Paused = 'paused';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Archived = 'archived';

    public static function normalize(null|string|self $status): self
    {
        if ($status instanceof self) {
            return $status;
        }

        $value = is_string($status) ? strtolower(trim($status)) : '';

        return match ($value) {
            '', 'draft' => self::Draft,
            'scheduled', 'schedule' => self::Scheduled,
            'running', 'run', 'processing' => self::Running,
            'paused', 'pause' => self::Paused,
            'completed', 'complete', 'done' => self::Completed,
            'cancelled', 'canceled', 'cancel' => self::Cancelled,
            'archived', 'archive' => self::Archived,
            default => throw new InvalidArgumentException("Unknown campaign status [{$status}]."),
        };
    }
}
