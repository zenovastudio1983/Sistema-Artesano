<?php

namespace App\Support\Enums;

enum PurchaseOrderStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case PartiallyReceived = 'partially_received';
    case Received = 'received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::Draft => 'Borrador',
            self::Sent => 'Enviada',
            self::PartiallyReceived => 'Recibida Parcial',
            self::Received => 'Recibida',
            self::Cancelled => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Draft => 'gray',
            self::Sent => 'blue',
            self::PartiallyReceived => 'yellow',
            self::Received => 'green',
            self::Cancelled => 'red',
        };
    }

    public function canReceive(): bool
    {
        return in_array($this, [self::Sent, self::PartiallyReceived]);
    }
}
