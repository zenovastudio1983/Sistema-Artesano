<?php

namespace App\Support\Enums;

enum ProductType: string
{
    case RawMaterial = 'raw_material';
    case FinishedProduct = 'finished_product';
    case SemiFinished = 'semi_finished';
    case Packaging = 'packaging';
    case Supply = 'supply';

    public function label(): string
    {
        return match($this) {
            self::RawMaterial => 'Materia Prima',
            self::FinishedProduct => 'Producto Terminado',
            self::SemiFinished => 'Semiterminado',
            self::Packaging => 'Envase/Empaque',
            self::Supply => 'Suministro',
        };
    }

    public function canHaveRecipe(): bool
    {
        return in_array($this, [self::FinishedProduct, self::SemiFinished]);
    }
}
