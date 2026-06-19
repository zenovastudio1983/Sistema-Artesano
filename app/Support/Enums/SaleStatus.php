<?php

namespace App\Support\Enums;

enum SaleStatus: string
{
    case Quotation = 'quotation';
    case Confirmed = 'confirmed';
    case Invoiced = 'invoiced';
    case Paid = 'paid';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Quotation => 'Cotización',
            self::Confirmed => 'Confirmada',
            self::Invoiced => 'Facturada',
            self::Paid => 'Pagada',
            self::Cancelled => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Quotation => 'gray',
            self::Confirmed => 'blue',
            self::Invoiced => 'yellow',
            self::Paid => 'green',
            self::Cancelled => 'red',
        };
    }

    public function affectsStock(): bool
    {
        return $this === self::Confirmed;
    }
}
