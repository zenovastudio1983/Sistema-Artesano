<?php

namespace App\Support\Enums;

enum CostMethod: string
{
    case Average = 'average';
    case Standard = 'standard';
    case Real = 'real';

    public function label(): string
    {
        return match($this) {
            self::Average => 'Costo Promedio Ponderado',
            self::Standard => 'Costo Estándar',
            self::Real => 'Costo Real (FIFO)',
        };
    }
}
