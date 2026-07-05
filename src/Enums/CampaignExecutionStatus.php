<?php

declare(strict_types=1);

namespace LBHurtado\XCampaign\Enums;

use InvalidArgumentException;

enum CampaignExecutionStatus: string
{
    case Planned = 'planned';
    case Queued = 'queued';
    case Running = 'running';
    case Paused = 'paused';
    case Completed = 'completed';
    case Failed = 'failed';
    case Cancelled = 'cancelled';

    public static function normalize(null|string|self $status): self
    {
        if ($status instanceof self) {
            return $status;
        }

        $value = is_string($status) ? strtolower(trim($status)) : '';

        return match ($value) {
            '', 'planned', 'plan' => self::Planned,
            'queued', 'queue' => self::Queued,
            'running', 'run', 'processing' => self::Running,
            'paused', 'pause' => self::Paused,
            'completed', 'complete', 'done' => self::Completed,
            'failed', 'fail' => self::Failed,
            'cancelled', 'canceled', 'cancel' => self::Cancelled,
            default => throw new InvalidArgumentException("Unknown campaign execution status [{$status}]."),
        };
    }
}
