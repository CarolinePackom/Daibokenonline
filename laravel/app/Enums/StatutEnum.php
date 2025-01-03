<?php

namespace App\Enums;

enum StatutEnum: string
{
    case Preparation = 'preparation';

    case Pret = 'pret';

    public function getLabel(): string
    {
        return match ($this) {
            self::Preparation => 'Préparation',
            self::Pret => 'Prêt',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::Preparation => 'info',
            self::Pret => 'success',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Preparation => 'heroicon-m-arrow-path',
            self::Pret => 'heroicon-m-check-badge',
        };
    }
}
