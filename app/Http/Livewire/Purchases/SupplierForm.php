<?php

namespace App\Http\Livewire\Purchases;

use App\Domains\Purchases\Models\Supplier;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Proveedor')]
class SupplierForm extends Component
{
    public ?int $supplierId = null;

    public string $business_name = '';
    public string $trade_name = '';
    public string $tax_id = '';
    public string $tax_type = 'ruc';
    public string $email = '';
    public string $phone = '';
    public string $mobile = '';
    public string $website = '';
    public string $address = '';
    public string $city = '';
    public string $country = 'PE';
    public string $contact_name = '';
    public string $contact_email = '';
    public string $contact_phone = '';
    public string $payment_days = '30';
    public string $currency = 'PEN';
    public string $credit_limit = '0';
    public string $notes = '';
    public bool $is_active = true;

    public function mount(?Supplier $supplier = null): void
    {
        if ($supplier && $supplier->exists) {
            $this->supplierId   = $supplier->id;
            $this->business_name  = $supplier->business_name;
            $this->trade_name     = $supplier->trade_name ?? '';
            $this->tax_id         = $supplier->tax_id ?? '';
            $this->tax_type       = $supplier->tax_type ?? 'ruc';
            $this->email          = $supplier->email ?? '';
            $this->phone          = $supplier->phone ?? '';
            $this->mobile         = $supplier->mobile ?? '';
            $this->website        = $supplier->website ?? '';
            $this->address        = $supplier->address ?? '';
            $this->city           = $supplier->city ?? '';
            $this->country        = $supplier->country ?? 'PE';
            $this->contact_name   = $supplier->contact_name ?? '';
            $this->contact_email  = $supplier->contact_email ?? '';
            $this->contact_phone  = $supplier->contact_phone ?? '';
            $this->payment_days   = (string) ($supplier->payment_days ?? 30);
            $this->currency       = $supplier->currency ?? 'PEN';
            $this->credit_limit   = (string) ($supplier->credit_limit ?? 0);
            $this->notes          = $supplier->notes ?? '';
            $this->is_active      = $supplier->is_active;
        }
    }

    protected function rules(): array
    {
        return [
            'business_name' => 'required|string|max:200',
            'trade_name'    => 'nullable|string|max:200',
            'tax_id'        => 'nullable|string|max:20',
            'email'         => 'nullable|email|max:150',
            'phone'         => 'nullable|string|max:30',
            'mobile'        => 'nullable|string|max:30',
            'website'       => 'nullable|url|max:200',
            'city'          => 'nullable|string|max:100',
            'contact_name'  => 'nullable|string|max:150',
            'contact_email' => 'nullable|email|max:150',
            'payment_days'  => 'nullable|integer|min:0|max:365',
            'credit_limit'  => 'nullable|numeric|min:0',
            'notes'         => 'nullable|string|max:1000',
            'is_active'     => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'business_name' => $this->business_name,
            'trade_name'    => $this->trade_name ?: null,
            'tax_id'        => $this->tax_id ?: null,
            'tax_type'      => $this->tax_type ?: null,
            'email'         => $this->email ?: null,
            'phone'         => $this->phone ?: null,
            'mobile'        => $this->mobile ?: null,
            'website'       => $this->website ?: null,
            'address'       => $this->address ?: null,
            'city'          => $this->city ?: null,
            'country'       => $this->country,
            'contact_name'  => $this->contact_name ?: null,
            'contact_email' => $this->contact_email ?: null,
            'contact_phone' => $this->contact_phone ?: null,
            'payment_days'  => $this->payment_days ? (int) $this->payment_days : 30,
            'currency'      => $this->currency,
            'credit_limit'  => (float) $this->credit_limit,
            'notes'         => $this->notes ?: null,
            'is_active'     => $this->is_active,
        ];

        if ($this->supplierId) {
            Supplier::findOrFail($this->supplierId)->update($data);
            session()->flash('success', 'Proveedor actualizado.');
        } else {
            Supplier::create($data);
            session()->flash('success', 'Proveedor creado.');
        }

        $this->redirect(route('suppliers.index'));
    }

    public function render()
    {
        return view('livewire.purchases.supplier-form');
    }
}
