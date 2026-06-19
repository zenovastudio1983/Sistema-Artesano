<?php

namespace App\Support\Enums;

enum ProductStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Discontinued = 'discontinued';

    public function label(): string
    {
        return match($this) {
            self::Active => 'Activo',
            self::Inactive => 'Inactivo',
            self::Discontinued => 'Descontinuado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Active => 'green',
            self::Inactive => 'gray',
            self::Discontinued => 'red',
        };
    }
}
