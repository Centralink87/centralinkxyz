<?php

namespace App\Enum;

enum RequestType: string
{
    case DEPOSIT = 'deposit';
    case WITHDRAWAL = 'withdrawal';

    public function label(): string
    {
        return match ($this) {
            self::DEPOSIT => 'Dépôt',
            self::WITHDRAWAL => 'Retrait',
        };
    }
}

