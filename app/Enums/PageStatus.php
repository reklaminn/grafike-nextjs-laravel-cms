<?php

namespace App\Enums;

enum PageStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Published = 'published';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Taslak',
            self::Scheduled => 'Zamanlanmış',
            self::Published => 'Yayında',
            self::Archived => 'Arşivlenmiş',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'yellow',
            self::Scheduled => 'blue',
            self::Published => 'green',
            self::Archived => 'gray',
        };
    }
}
