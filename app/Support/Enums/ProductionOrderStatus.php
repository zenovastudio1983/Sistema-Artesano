<?php

namespace App\Support\Enums;

enum ProductionOrderStatus: string
{
    case Draft = 'draft';
    case Planned = 'planned';
    case InProgress = 'in_progress';
    case Finished = 'finished';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Borrador',
            self::Planned => 'Planificada',
            self::InProgress => 'En Proceso',
            self::Finished => 'Finalizada',
            self::Cancelled => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft => 'gray',
            self::Planned => 'blue',
            self::InProgress => 'yellow',
            self::Finished => 'green',
            self::Cancelled => 'red',
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return match($this) {
            self::Draft => in_array($next, [self::Planned, self::Cancelled]),
            self::Planned => in_array($next, [self::InProgress, self::Cancelled]),
            self::InProgress => in_array($next, [self::Finished, self::Cancelled]),
            self::Finished, self::Cancelled => false,
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Planned]);
    }
}
