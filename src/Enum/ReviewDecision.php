<?php

namespace App\Enum;

enum ReviewDecision: string
{
    case PENDING = 'pending';
    case ACCEPT = 'accept';
    case MINOR_REVISION = 'minor_revision';
    case MAJOR_REVISION = 'major_revision';
    case REJECT = 'reject';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Oczekująca',
            self::ACCEPT => 'Akceptacja',
            self::MINOR_REVISION => 'Drobne poprawki',
            self::MAJOR_REVISION => 'Znaczące poprawki',
            self::REJECT => 'Odrzucenie',
        };
    }
}
