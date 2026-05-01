<?php

namespace Platform\Commerce\Enums;

enum SupplierStatus: string
{
    case Onboarding = 'onboarding';
    case Active = 'active';
    case Paused = 'paused';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Onboarding => 'Onboarding',
            self::Active => 'Aktiv',
            self::Paused => 'Pausiert',
            self::Archived => 'Archiviert',
        };
    }
}
