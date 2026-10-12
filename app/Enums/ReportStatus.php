<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Pending = 'pending';
    case NeedsClarification = 'needs_clarification';
    case Verified = 'verified';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Rejected = 'rejected';

    public function canTransitionTo(self $status): bool
    {
        return match ($this) {
            self::Pending => in_array($status, [self::NeedsClarification, self::Verified, self::Rejected], true),
            self::NeedsClarification => in_array($status, [self::Pending, self::Verified, self::Rejected], true),
            self::Verified => in_array($status, [self::Completed, self::Rejected], true),
            self::InProgress => in_array($status, [self::Completed, self::Rejected], true),
            self::Completed, self::Rejected => false,
        };
    }
}
