<?php

namespace App\Support\Enums;

enum MovementType: string
{
    case PurchaseEntry = 'purchase_entry';
    case ProductionEntry = 'production_entry';
    case SaleExit = 'sale_exit';
    case ProductionConsumption = 'production_consumption';
    case PositiveAdjustment = 'positive_adjustment';
    case NegativeAdjustment = 'negative_adjustment';
    case TransferIn = 'transfer_in';
    case TransferOut = 'transfer_out';
    case ReturnToSupplier = 'return_to_supplier';
    case CustomerReturn = 'customer_return';
    case InitialStock = 'initial_stock';

    public function label(): string
    {
        return match($this) {
            self::PurchaseEntry => 'Ingreso por Compra',
            self::ProductionEntry => 'Ingreso por Producción',
            self::SaleExit => 'Salida por Venta',
            self::ProductionConsumption => 'Consumo en Producción',
            self::PositiveAdjustment => 'Ajuste Positivo',
            self::NegativeAdjustment => 'Ajuste Negativo',
            self::TransferIn => 'Transferencia Entrada',
            self::TransferOut => 'Transferencia Salida',
            self::ReturnToSupplier => 'Devolución a Proveedor',
            self::CustomerReturn => 'Devolución de Cliente',
            self::InitialStock => 'Stock Inicial',
        };
    }

    public function isEntry(): bool
    {
        return in_array($this, [
            self::PurchaseEntry,
            self::ProductionEntry,
            self::PositiveAdjustment,
            self::TransferIn,
            self::CustomerReturn,
            self::InitialStock,
        ]);
    }

    public function isExit(): bool
    {
        return !$this->isEntry();
    }

    public function color(): string
    {
        return $this->isEntry() ? 'green' : 'red';
    }
}
